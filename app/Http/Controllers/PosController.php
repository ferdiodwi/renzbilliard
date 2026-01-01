<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    /**
     * Get active products for POS
     */
    public function products(Request $request)
    {
        $query = Product::with('category')->available();

        if ($request->has('category')) {
            $query->byCategory($request->category);
        }

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $products = $query->orderBy('category_id')->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    /**
     * Create a new F&B order
     */
    public function createOrder(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'nullable|string|max:255',
            'session_id' => 'nullable|exists:sessions_billiard,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        try {
            DB::beginTransaction();

            // Calculate totals
            $subtotal = 0;
            $orderItems = [];

            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);

                // Check stock
                if (!$product->isInStock()) {
                    return response()->json([
                        'success' => false,
                        'message' => "Product {$product->name} is out of stock",
                    ], 400);
                }

                $itemSubtotal = $product->price * $item['quantity'];
                $subtotal += $itemSubtotal;

                $orderItems[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                    'subtotal' => $itemSubtotal,
                ];

                // Reduce stock
                $product->reduceStock($item['quantity']);
            }

            // Calculate tax (0%)
            $tax = 0;
            $total = $subtotal + $tax;

            // Get customer name from session if linked
            $customerName = $validated['customer_name'] ?? null;
            if (!$customerName && $validated['session_id']) {
                $session = \App\Models\SessionBilliard::find($validated['session_id']);
                $customerName = $session->customer_name ?? null;
            }

            // Create order
            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'customer_name' => $customerName,
                'session_id' => $validated['session_id'] ?? null,
                'cashier_id' => auth()->id(),
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total,
                'status' => 'pending', // Default to pending for POS orders until paid
            ]);

            // Create order items
            foreach ($orderItems as $item) {
                $order->items()->create($item);
            }

            DB::commit();

            // Load relationships
            $order->load(['items.product', 'cashier', 'session']);

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'data' => $order,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create order: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get order history
     */
    public function orders(Request $request)
    {
        $query = Order::with(['items.product', 'cashier', 'session.table'])
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        } else {
            // By default, exclude cancelled orders
            $query->whereIn('status', ['pending', 'completed']);
        }

        // Filter by date range
        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        // Search by Order Number or Customer Name
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhereHas('session', function($sq) use ($search) {
                      $sq->where('customer_name', 'like', "%{$search}%");
                  });
            });
        }

        $perPage = $request->input('per_page', 20);
        $orders = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $orders->items(),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    /**
     * Get order details
     */
    public function showOrder($id)
    {
        $order = Order::with(['items.product', 'cashier', 'session'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $order,
        ]);
    }

    /**
     * Pay for standalone F&B order
     */
    public function payOrder(Request $request, $id)
    {
        $request->validate([
            'payment_method' => 'required|in:cash,qris,transfer',
        ]);

        $order = Order::with(['items.product'])->findOrFail($id);

        // Only allow payment for standalone orders (not linked to session)
        if ($order->session_id) {
            return response()->json([
                'success' => false,
                'message' => 'Order ini sudah ter-link ke session. Bayar saat stop session.',
            ], 422);
        }

        // Check if already paid based on order status
        if ($order->status === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Order sudah dibayar',
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Create transaction
            $transaction = \App\Models\Transaction::create([
                'invoice_number' => \App\Models\Transaction::generateInvoiceNumber(),
                'total_amount' => $order->total,
                'payment_method' => $request->payment_method,
                'paid_at' => now(),
                'cashier_id' => auth()->id(),
            ]);

            // Create transaction items for each order item
            foreach ($order->items as $item) {
                \App\Models\TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'type' => 'product',
                    'session_id' => null,
                    'product_id' => $item->product_id,
                    'price' => $item->subtotal,
                    'quantity' => $item->quantity,
                ]);
            }

            // Mark order as completed and save payment method
            $order->update([
                'status' => 'completed',
                'payment_method' => $request->payment_method,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil',
                'data' => [
                    'transaction' => $transaction,
                    'invoice_number' => $transaction->invoice_number,
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses pembayaran: ' . $e->getMessage(),
            ], 500);
        }
    }
}
