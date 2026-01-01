<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    /**
     * List expenses with filters and pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Expense::with('user')->orderBy('expense_date', 'desc');

        // Date filters
        if ($request->has('date_from')) {
            $query->whereDate('expense_date', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->whereDate('expense_date', '<=', $request->date_to);
        }

        // Category filter
        if ($request->has('category') && $request->category) {
            $query->where('category', $request->category);
        }

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        // Stats
        $statsQuery = clone $query;
        $stats = [
            'total_expenses' => $statsQuery->count(),
            'total_amount' => $statsQuery->sum('amount'),
        ];

        // Pagination
        $perPage = $request->input('per_page', 10);
        $perPage = in_array($perPage, [10, 20, 50, 100]) ? $perPage : 10;
        
        $expenses = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $expenses,
            'stats' => $stats,
        ]);
    }

    /**
     * Store new expense.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'category' => 'required|in:operasional,gaji,pembelian_stok,lainnya',
            'payment_method' => 'required|in:cash,qris',
            'expense_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $validated['user_id'] = $request->user()->id;

        $expense = Expense::create($validated);
        $expense->load('user');

        return response()->json([
            'success' => true,
            'message' => 'Pengeluaran berhasil ditambahkan',
            'data' => $expense,
        ], 201);
    }

    /**
     * Show expense detail.
     */
    public function show(Expense $expense): JsonResponse
    {
        $expense->load('user');

        return response()->json([
            'success' => true,
            'data' => $expense,
        ]);
    }

    /**
     * Update expense.
     */
    public function update(Request $request, Expense $expense): JsonResponse
    {
        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'category' => 'required|in:operasional,gaji,pembelian_stok,lainnya',
            'payment_method' => 'required|in:cash,qris',
            'expense_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $expense->update($validated);
        $expense->load('user');

        return response()->json([
            'success' => true,
            'message' => 'Pengeluaran berhasil diupdate',
            'data' => $expense,
        ]);
    }

    /**
     * Delete expense.
     */
    public function destroy(Expense $expense): JsonResponse
    {
        $expense->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pengeluaran berhasil dihapus',
        ]);
    }
}
