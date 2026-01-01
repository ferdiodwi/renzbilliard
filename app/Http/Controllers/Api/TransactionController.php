<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SessionBilliard;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class TransactionController extends Controller
{
    /**
     * List transactions with filters.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Transaction::with(['cashier', 'items.session.table', 'items.product'])
            ->orderBy('paid_at', 'desc');

        // Date filter
        if ($request->has('date_from')) {
            $query->whereDate('paid_at', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->whereDate('paid_at', '<=', $request->date_to);
        }

        // Payment method filter
        if ($request->has('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        // Filter by transaction type (has session or not)
        if ($request->has('type') && $request->type === 'session') {
            $query->whereHas('items', function($q) {
                $q->whereNotNull('session_id');
            });
        }

        // Search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                // Search by invoice number
                $q->where('invoice_number', 'like', "%{$search}%")
                  // Search by cashier name
                  ->orWhereHas('cashier', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  })
                  // Search by customer name in session
                  ->orWhereHas('items.session', function($q) use ($search) {
                      $q->where('customer_name', 'like', "%{$search}%");
                  });
            });
        }

        // Calculate stats from ALL filtered data (before pagination)
        $statsQuery = clone $query;
        $stats = [
            'total_transactions' => $statsQuery->count(),
            'total_revenue' => $statsQuery->sum('total_amount'),
            'cash_total' => (clone $statsQuery)->where('payment_method', 'cash')->sum('total_amount'),
            'non_cash_total' => (clone $statsQuery)->where('payment_method', '!=', 'cash')->sum('total_amount'),
        ];

        // Get per_page from request with validation
        $perPage = $request->input('per_page', 10);
        $perPage = in_array($perPage, [10, 20, 50, 100]) ? $perPage : 10;

        $transactions = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $transactions,
            'stats' => $stats,
        ]);
    }

    /**
     * Create a new transaction from finished sessions.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'session_ids' => 'required|array|min:1',
            'session_ids.*' => 'exists:sessions_billiard,id',
            'payment_method' => 'required|in:cash,qris,transfer',
        ]);

        // Get finished or playing sessions that haven't been paid
        $sessions = SessionBilliard::whereIn('id', $request->session_ids)
            ->whereIn('status', ['finished', 'playing'])
            ->whereDoesntHave('transactionItem')
            ->get();

        // Get F&B orders linked to these sessions (pending only)
        $fnbOrders = \App\Models\Order::whereIn('session_id', $request->session_ids)
            ->where('status', 'pending')
            ->with('items.product')
            ->get();

        if ($sessions->isEmpty() && $fnbOrders->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada tagihan yang perlu dibayar.',
            ], 422);
        }

        $totalAmount = $sessions->sum('total_price');
        $fnbTotal = $fnbOrders->sum('total');
        $totalAmount += $fnbTotal;

        DB::beginTransaction();
        try {
            $transaction = Transaction::create([
                'invoice_number' => Transaction::generateInvoiceNumber(),
                'total_amount' => $totalAmount,
                'payment_method' => $request->payment_method,
                'paid_at' => now(),
                'cashier_id' => $request->user()->id,
            ]);

            // Add session items
            foreach ($sessions as $session) {
                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'type' => 'session',
                    'session_id' => $session->id,
                    'product_id' => null,
                    'price' => $session->total_price,
                ]);
            }
            
            // Add F&B product items
            foreach ($fnbOrders as $order) {
                // Update order status to completed (paid)
                $order->update(['status' => 'completed']);
                
                foreach ($order->items as $item) {
                    TransactionItem::create([
                        'transaction_id' => $transaction->id,
                        'type' => 'product',
                        'session_id' => $order->session_id,
                        'product_id' => $item->product_id,
                        'price' => $item->subtotal,
                        'quantity' => $item->quantity,
                    ]);
                }
            }

            DB::commit();

            $transaction->load(['items.session.table', 'items.session.rate', 'items.product', 'cashier']);

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil dibuat',
                'data' => [
                    'id' => $transaction->id,
                    'invoice_number' => $transaction->invoice_number,
                    'total_amount' => $transaction->total_amount,
                    'payment_method' => $transaction->payment_method,
                    'paid_at' => $transaction->paid_at->toIso8601String(),
                    'cashier' => $transaction->cashier->name,
                    'items' => $transaction->items->map(function ($item) {
                        return self::formatTransactionItem($item);
                    }),
                ],
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat transaksi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show transaction details.
     */
    public function show(Transaction $transaction): JsonResponse
    {
        $transaction->load(['items.session.table', 'items.session.rate', 'items.product', 'cashier']);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $transaction->id,
                'invoice_number' => $transaction->invoice_number,
                'total_amount' => $transaction->total_amount,
                'payment_method' => $transaction->payment_method,
                'paid_at' => $transaction->paid_at->toIso8601String(),
                'cashier' => $transaction->cashier->name,
                'items' => $transaction->items->map(function ($item) {
                    return self::formatTransactionItem($item);
                }),
            ],
        ]);
    }

    /**
     * Helper to format transaction item
     */
    private static function formatTransactionItem($item)
    {
        $data = [
            'type' => $item->type,
            'price' => $item->price,
        ];

        if ($item->type === 'session' && $item->session) {
            $data['session'] = [
                'table_number' => $item->session->table->table_number ?? '?',
                'rate_name' => $item->session->rate->name ?? '?',
                'start_time' => $item->session->start_time->toIso8601String(),
                'end_time' => $item->session->end_time->toIso8601String(),
                'duration_minutes' => $item->session->duration_minutes,
            ];
        }

        if ($item->type === 'product' && $item->product) {
            $data['product'] = [
                'name' => $item->product->name,
                'category' => $item->product->category,
            ];
            $data['quantity'] = $item->quantity; // Add quantity field
            // Also include session info if needed (e.g. table number)
            if ($item->session) {
                $data['session'] = [
                    'table_number' => $item->session->table->table_number ?? '?',
                    'customer_name' => $item->session->customer_name,
                ];
            }
        }

        return $data;
    }

    /**
     * Download invoice PDF.
     */
    public function invoice(Transaction $transaction)
    {
        $transaction->load(['items.session.table', 'items.session.rate', 'cashier']);

        $pdf = Pdf::loadView('invoices.receipt', [
            'transaction' => $transaction,
        ]);

        return $pdf->download("invoice-{$transaction->invoice_number}.pdf");
    }

    /**
     * Get unpaid finished sessions.
     */
    public function unpaidSessions(): JsonResponse
    {
        $sessions = SessionBilliard::with(['table', 'rate'])
            ->where('status', 'finished')
            ->whereDoesntHave('transactionItem')
            ->orderBy('end_time', 'desc')
            ->get()
            ->map(function ($session) {
                return [
                    'id' => $session->id,
                    'table_number' => $session->table->table_number,
                    'rate_name' => $session->rate->name,
                    'start_time' => $session->start_time->toIso8601String(),
                    'end_time' => $session->end_time->toIso8601String(),
                    'duration_minutes' => $session->duration_minutes,
                    'total_price' => $session->total_price,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $sessions,
        ]);
    }
    /**
     * Delete transaction.
     */
    public function destroy(Transaction $transaction): JsonResponse
    {
        DB::beginTransaction();
        try {
            // Get all related sessions from transaction items before deleting
            $sessionIds = $transaction->items()
                ->whereNotNull('session_id')
                ->pluck('session_id')
                ->unique();
            
            // Reset sessions to 'cancelled' status to prevent them from appearing as unpaid
            if ($sessionIds->isNotEmpty()) {
                SessionBilliard::whereIn('id', $sessionIds)
                    ->update(['status' => 'cancelled']);
            }
            
            // Get related orders and set to cancelled
            $orderIds = $transaction->items()
                ->whereHas('session.orders')
                ->get()
                ->flatMap(function($item) {
                    return $item->session->orders->pluck('id');
                })
                ->unique();
            
            if ($orderIds->isNotEmpty()) {
                \App\Models\Order::whereIn('id', $orderIds)
                    ->where('status', 'completed')
                    ->update(['status' => 'cancelled']);
            }
                
            $transaction->items()->delete();
            $transaction->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus transaksi: ' . $e->getMessage(),
            ], 500);
        }
    }
}
