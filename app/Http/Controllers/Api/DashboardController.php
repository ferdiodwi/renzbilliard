<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SessionBilliard;
use App\Models\TableBilliard;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics.
     */
    public function stats(): JsonResponse
    {
        $today = Carbon::today();

        $todayRevenue = Transaction::whereDate('paid_at', $today)
            ->sum('total_amount');

        $activeSessions = SessionBilliard::active()->count();

        $totalTables = TableBilliard::count();
        $availableTables = TableBilliard::where('status', 'available')->count();

        // Unpaid sessions: Finished sessions that don't have a transaction item
        $unpaidSessions = SessionBilliard::where('status', 'finished')
            ->doesntHave('transactionItem')
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'today' => [
                    'revenue' => $todayRevenue,
                ],
                'active_sessions' => $activeSessions,
                'tables' => [
                    'total' => $totalTables,
                    'available' => $availableTables,
                ],
                'unpaid_sessions' => $unpaidSessions,
            ],
        ]);
    }

    /**
     * Get chart data.
     */
    public function chart(Request $request): JsonResponse
    {
        $period = $request->query('period', 'week');
        $data = [];

        switch ($period) {
            case 'month':
                $start = Carbon::now()->startOfMonth();
                $end = Carbon::now()->endOfMonth();
                $format = 'Y-m-d';
                $labelFormat = 'd M';
                break;
            case 'year':
                $start = Carbon::now()->startOfYear();
                $end = Carbon::now()->endOfYear();
                $format = 'Y-m';
                $labelFormat = 'M Y';
                break;
            case 'week':
            default:
                $start = Carbon::now()->subDays(6)->startOfDay();
                $end = Carbon::now()->endOfDay();
                $format = 'Y-m-d';
                $labelFormat = 'd M';
                break;
        }

        // Transactions for revenue
        $transactions = Transaction::select(
            DB::raw("DATE_FORMAT(paid_at, '%Y-%m-%d') as date"), // Use standard date format for grouping first
            DB::raw('SUM(total_amount) as total')
        )
            ->whereBetween('paid_at', [$start, $end])
            ->groupBy('date')
            ->get()
            ->keyBy('date');
            
        // If year view, we need to group by month in PHP or adjust query. 
        // For simplicity let's stick to day grouping for week/month.
        // For year, we should group by month.
        
        if ($period === 'year') {
             $transactions = Transaction::select(
                DB::raw("DATE_FORMAT(paid_at, '%Y-%m') as date"),
                DB::raw('SUM(total_amount) as total')
            )
                ->whereBetween('paid_at', [$start, $end])
                ->groupBy('date')
                ->get()
                ->keyBy('date');
        }


        // Sessions count
        $sessionsQuery = SessionBilliard::select(
             DB::raw("DATE_FORMAT(start_time, '%Y-%m-%d') as date"),
             DB::raw('COUNT(*) as count')
        )
             ->whereBetween('start_time', [$start, $end])
             ->groupBy('date');
             
        if ($period === 'year') {
            $sessionsQuery = SessionBilliard::select(
                DB::raw("DATE_FORMAT(start_time, '%Y-%m') as date"),
                DB::raw('COUNT(*) as count')
            )
                ->whereBetween('start_time', [$start, $end])
                ->groupBy('date');
        }
        
        $sessions = $sessionsQuery->get()->keyBy('date');


        // Fill gaps
        $current = $start->copy();
        while ($current <= $end) {
            $key = $current->format($period === 'year' ? 'Y-m' : 'Y-m-d');
            $label = $current->format($labelFormat);

            $data[] = [
                'label' => $label,
                'revenue' => (float) ($transactions[$key]->total ?? 0),
                'sessions' => (int) ($sessions[$key]->count ?? 0),
            ];

            if ($period === 'year') {
                $current->addMonth();
            } else {
                $current->addDay();
            }
        }

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Get active alerts.
     */
    public function alerts(): JsonResponse
    {
        // 1. Check for sessions ending soon (less than 5 minutes)
        // We calculate the end time based on start_time + duration_minutes
        // Then filter those where end_time > now and end_time < now + 5 mins
        
        $sessions = SessionBilliard::active()->get();
        $expiringSessions = [];
        $now = Carbon::now();

        foreach ($sessions as $session) {
            $startTime = Carbon::parse($session->start_time);
            $endTime = $startTime->copy()->addMinutes($session->duration_minutes);
            
            // Calculate remaining time in minutes (can be negative if expired)
            $remainingMinutes = $now->diffInMinutes($endTime, false);
            
            // Include sessions that are:
            // 1. Expiring within 5 minutes
            // 2. Already expired but still marked as active
            if ($remainingMinutes <= 5) {
                $expiringSessions[] = [
                    'id' => $session->id,
                    'table_number' => $session->table->table_number ?? '?',
                    'customer_name' => $session->customer_name,
                    'remaining_minutes' => max(0, $remainingMinutes), // Don't send negative
                    'is_expired' => $remainingMinutes <= 0,
                ];
            }
        }

        // 2. Get latest transaction for new order alerts
        $latestTransaction = Transaction::latest('id')->first();

        return response()->json([
            'success' => true,
            'data' => [
                'expiring_sessions' => $expiringSessions,
                'latest_transaction_id' => $latestTransaction ? $latestTransaction->id : 0,
            ],
        ]);
    }
}
