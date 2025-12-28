<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SessionBilliard;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Get report statistics (API).
     */
    public function index(Request $request): JsonResponse
    {
        $data = $this->getReportData($request);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Export report to PDF or Excel (CSV).
     */
    public function export(Request $request)
    {
        $data = $this->getReportData($request);
        $type = $request->query('type', 'pdf');
        $startDate = Carbon::parse($data['period']['start'])->format('d M Y');
        $endDate = Carbon::parse($data['period']['end'])->format('d M Y');
        $fileName = "Laporan_RenzBilliard_{$startDate}-{$endDate}";

        if ($type === 'excel') {
            return $this->exportCsv($data, $fileName);
        }

        // PDF download
        $pdf = Pdf::loadView('reports.pdf', ['data' => $data]);
        return $pdf->download("{$fileName}.pdf");
    }

    /**
     * Shared logic to fetch report data.
     */
    private function getReportData(Request $request): array
    {
        $startDate = $request->query('start_date') 
            ? Carbon::parse($request->query('start_date'))->startOfDay() 
            : Carbon::now()->startOfMonth();
            
        $endDate = $request->query('end_date') 
            ? Carbon::parse($request->query('end_date'))->endOfDay() 
            : Carbon::now()->endOfDay();

        // 1. Summary Stats
        $totalRevenue = Transaction::whereBetween('paid_at', [$startDate, $endDate])->sum('total_amount');
        $totalSessions = SessionBilliard::whereBetween('start_time', [$startDate, $endDate])->count();
        $totalTransactions = Transaction::whereBetween('paid_at', [$startDate, $endDate])->count();

        // 2. Daily Chart Data
        $transactions = Transaction::select(
            DB::raw("DATE_FORMAT(paid_at, '%Y-%m-%d') as date"),
            DB::raw('SUM(total_amount) as total')
        )
            ->whereBetween('paid_at', [$startDate, $endDate])
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        $chartData = [];
        $current = $startDate->copy();
        while ($current <= $endDate) {
            $key = $current->format('Y-m-d');
            $chartData[] = [
                'date' => $key,
                'label' => $current->format('d M'),
                'revenue' => (float) ($transactions[$key]->total ?? 0),
            ];
            $current->addDay();
        }

        // 3. Transactions List
        $recentTransactions = Transaction::with('cashier')
            ->whereBetween('paid_at', [$startDate, $endDate])
            ->latest('paid_at')
            ->paginate(10);

        // 4. Revenue Breakdown (Billiard vs FnB)
        $revenueByCategory = [
            'billiard' => 0,
            'fnb' => 0
        ];
        
        // Sum items linked to transactions in range
        $items = TransactionItem::whereHas('transaction', function($q) use ($startDate, $endDate) {
            $q->whereBetween('paid_at', [$startDate, $endDate]);
        })->get();

        foreach ($items as $item) {
            // Check based on type or product_id to ensure F&B ordered during session counts as F&B
            if ($item->type === 'product' || $item->product_id) {
                $revenueByCategory['fnb'] += $item->price;
            } else {
                // Otherwise it's the table rental fee
                $revenueByCategory['billiard'] += $item->price;
            }
        }

        // 5. Payment Method Stats
        $paymentStats = Transaction::select('payment_method', DB::raw('count(*) as count'), DB::raw('sum(total_amount) as total'))
            ->whereBetween('paid_at', [$startDate, $endDate])
            ->groupBy('payment_method')
            ->get();

        return [
            'summary' => [
                'revenue' => $totalRevenue,
                'sessions' => $totalSessions,
                'transactions' => $totalTransactions,
            ],
            'chart' => $chartData,
            'transactions' => $recentTransactions,
            'breakdown' => $revenueByCategory,
            'payment_methods' => $paymentStats,
            'period' => [
                'start' => $startDate->toDateString(),
                'end' => $endDate->toDateString()
            ]
        ];
    }

    private function exportCsv($data, $fileName)
    {
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename={$fileName}.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['Tanggal', 'Invoice', 'Kasir', 'Metode Bayar', 'Total'];

        $callback = function() use ($data, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($data['transactions'] as $tx) {
                fputcsv($file, [
                    $tx->paid_at->format('Y-m-d H:i'),
                    $tx->invoice_number,
                    $tx->cashier->name ?? '-',
                    $tx->payment_method,
                    $tx->total_amount
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
