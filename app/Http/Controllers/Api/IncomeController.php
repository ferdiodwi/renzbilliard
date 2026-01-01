<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class IncomeController extends Controller
{
    /**
     * Get income split by category (billiard vs F&B).
     * Even from the same transaction, billiard and F&B are shown as separate rows.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 10);
        $perPage = in_array($perPage, [10, 20, 50, 100]) ? $perPage : 10;
        $page = $request->input('page', 1);
        $type = $request->input('type'); // 'billiard' or 'fnb'
        $search = $request->input('search');

        $incomeItems = collect();

        // === 1. Get Billiard Income from TransactionItems (type = 'session') ===
        if ($type !== 'fnb') {
            $billiardItemsQuery = TransactionItem::with(['transaction.cashier', 'session.table'])
                ->where('type', 'session')
                ->whereHas('transaction');
            
            if ($search) {
                $billiardItemsQuery->whereHas('transaction', function($q) use ($search) {
                    $q->where('invoice_number', 'like', "%{$search}%");
                })->orWhereHas('session', function($q) use ($search) {
                    $q->where('customer_name', 'like', "%{$search}%");
                });
            }

            $billiardItems = $billiardItemsQuery->get()->map(function($item) {
                // Keep original INV-xxx format for billiard
                return [
                    'id' => 'session_' . $item->id,
                    'type' => 'billiard',
                    'date' => $item->transaction->paid_at,
                    'invoice' => $item->transaction->invoice_number,
                    'description' => 'Meja ' . ($item->session->table->table_number ?? '?'),
                    'customer' => $item->session->customer_name ?? null,
                    'payment_method' => $item->transaction->payment_method,
                    'amount' => $item->price,
                    'sort_date' => $item->transaction->paid_at,
                ];
            });
            $incomeItems = $incomeItems->merge($billiardItems);
        }

        // === 2. Get F&B Income from TransactionItems (type = 'product') ===
        if ($type !== 'billiard') {
            $fnbItemsQuery = TransactionItem::with(['transaction.cashier', 'product', 'session'])
                ->where('type', 'product')
                ->whereHas('transaction');
            
            if ($search) {
                $fnbItemsQuery->whereHas('transaction', function($q) use ($search) {
                    $q->where('invoice_number', 'like', "%{$search}%");
                })->orWhereHas('product', function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            }

            $fnbItems = $fnbItemsQuery->get()->map(function($item) {
                $qty = $item->quantity ?? 1;
                // Generate FNB invoice format based on transaction date + item ID
                $date = $item->transaction->paid_at->format('Ymd');
                $fnbInvoice = sprintf('FNB-%s-%04d', $date, $item->id);
                
                return [
                    'id' => 'product_' . $item->id,
                    'type' => 'fnb',
                    'date' => $item->transaction->paid_at,
                    'invoice' => $fnbInvoice,
                    'description' => ($item->product->name ?? 'Produk') . ' x' . $qty,
                    'customer' => $item->session->customer_name ?? null,
                    'payment_method' => $item->transaction->payment_method,
                    'amount' => $item->price,
                    'sort_date' => $item->transaction->paid_at,
                ];
            });
            $incomeItems = $incomeItems->merge($fnbItems);
        }

        // Note: Standalone F&B Orders are already captured in TransactionItems (type=product)
        // when paid via POS, so we don't need to query Orders separately

        // Sort by date descending
        $incomeItems = $incomeItems->sortByDesc('sort_date')->values();

        // Calculate stats
        $billiardTotal = $incomeItems->where('type', 'billiard')->sum('amount');
        $fnbTotal = $incomeItems->where('type', 'fnb')->sum('amount');

        // Apply type filter (after collecting all for stats)
        if ($type === 'billiard') {
            $incomeItems = $incomeItems->where('type', 'billiard')->values();
        } elseif ($type === 'fnb') {
            $incomeItems = $incomeItems->where('type', 'fnb')->values();
        }

        // Manual pagination
        $total = $incomeItems->count();
        $offset = ($page - 1) * $perPage;
        $pagedItems = $incomeItems->slice($offset, $perPage)->values();

        $paginator = new LengthAwarePaginator(
            $pagedItems,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return response()->json([
            'success' => true,
            'data' => $paginator,
            'stats' => [
                'total_income' => $billiardTotal + $fnbTotal,
                'billiard_income' => $billiardTotal,
                'fnb_income' => $fnbTotal,
            ],
        ]);
    }
}
