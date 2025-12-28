<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TableBilliard;
use App\Models\Rate;
use App\Models\SessionBilliard;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SessionController extends Controller
{
    /**
     * Get active sessions.
     */
    public function active(): JsonResponse
    {
        $sessions = SessionBilliard::with(['table', 'rate'])
            ->active()
            ->orderBy('end_time')
            ->get()
            ->map(function ($session) {
                return [
                    'id' => $session->id,
                    'table' => [
                        'id' => $session->table->id,
                        'table_number' => $session->table->table_number,
                    ],
                    'rate' => [
                        'id' => $session->rate->id,
                        'name' => $session->rate->name,
                        'price_per_hour' => $session->rate->price_per_hour,
                    ],
                    'start_time' => $session->start_time->toIso8601String(),
                    'end_time' => $session->end_time->toIso8601String(),
                    'duration_minutes' => $session->duration_minutes,
                    'remaining_seconds' => $session->remaining_seconds,
                    'total_price' => $session->total_price,
                    'auto_stop' => $session->auto_stop,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $sessions,
        ]);
    }

    /**
     * Start a new session.
     */
    public function start(Request $request): JsonResponse
    {
        $request->validate([
            'table_id' => 'required|exists:tables_billiard,id',
            'customer_name' => 'required|string|max:255',
            'rate_id' => 'required|exists:rates,id',
            'duration_minutes' => 'required|integer|min:2|max:480',
            'auto_stop' => 'boolean',
        ]);

        $table = TableBilliard::findOrFail($request->table_id);

        if (!$table->isAvailable()) {
            return response()->json([
                'success' => false,
                'message' => 'Meja tidak tersedia',
            ], 422);
        }

        $rate = Rate::findOrFail($request->rate_id);
        $startTime = Carbon::now();
        $endTime = $startTime->copy()->addMinutes($request->duration_minutes);
        $totalPrice = $rate->calculatePrice($request->duration_minutes);

        DB::beginTransaction();
        try {
            $session = SessionBilliard::create([
                'table_id' => $table->id,
                'customer_name' => $request->customer_name,
                'rate_id' => $rate->id,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'duration_minutes' => $request->duration_minutes,
                'total_price' => $totalPrice,
                'auto_stop' => $request->auto_stop ?? true,
                'status' => 'playing',
            ]);

            $table->update(['status' => 'playing']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Sesi berhasil dimulai',
                'data' => [
                    'id' => $session->id,
                    'table_number' => $table->table_number,
                    'rate_name' => $rate->name,
                    'start_time' => $session->start_time->toIso8601String(),
                    'end_time' => $session->end_time->toIso8601String(),
                    'duration_minutes' => $session->duration_minutes,
                    'total_price' => $session->total_price,
                ],
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memulai sesi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Stop a session manually.
     */
    public function stop(Request $request, $id)
    {
        $session = SessionBilliard::with(['table', 'rate'])->findOrFail($id);

        if ($session->status === 'finished') {
            return response()->json([
                'success' => false,
                'message' => 'Sesi sudah selesai',
            ], 422);
        }

        $now = Carbon::now();
        $endTime = $now;

        // If session has scheduled end time and we are past it, cap the billing
        if ($session->end_time && $now->greaterThan($session->end_time)) {
            $endTime = $session->end_time;
        }

        $actualDuration = $session->start_time->diffInMinutes($endTime);
        
        // Recalculate price based on capped duration
        $sessionPrice = $session->rate->calculatePrice($actualDuration);

        // Get F&B orders linked to this session
        $fnbOrders = Order::where('session_id', $session->id)
            ->whereIn('status', ['pending', 'completed'])
            ->get();
        
        $fnbTotal = $fnbOrders->sum('total');

        DB::beginTransaction();
        try {
            // Update session
            $session->update([
                'end_time' => $endTime,
                'duration_minutes' => $actualDuration,
                'total_price' => $sessionPrice,
                'status' => 'finished',
            ]);

            // Update table status
            $session->table->update(['status' => 'available']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Sesi berhasil dihentikan',
                'data' => [
                    'session' => $session,
                    'session_charges' => $sessionPrice,
                    'fnb_charges' => $fnbTotal,
                    'total_charges' => $sessionPrice + $fnbTotal,
                    'fnb_orders' => $fnbOrders->load('items.product'),
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghentikan sesi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Extend a session.
     */
    public function extend(Request $request, SessionBilliard $session): JsonResponse
    {
        $request->validate([
            'additional_minutes' => 'required|integer|min:15|max:240',
        ]);

        if ($session->status !== 'playing') {
            return response()->json([
                'success' => false,
                'message' => 'Sesi sudah selesai',
            ], 422);
        }

        $additionalMinutes = $request->additional_minutes;
        $additionalPrice = $session->rate->calculatePrice($additionalMinutes);

        $session->update([
            'end_time' => $session->end_time->addMinutes($additionalMinutes),
            'duration_minutes' => $session->duration_minutes + $additionalMinutes,
            'total_price' => $session->total_price + $additionalPrice,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Sesi berhasil diperpanjang',
            'data' => [
                'id' => $session->id,
                'new_end_time' => $session->end_time->toIso8601String(),
                'new_duration_minutes' => $session->duration_minutes,
                'new_total_price' => $session->total_price,
            ],
        ]);
    }

    /**
     * Get session details.
     */
    public function show(SessionBilliard $session): JsonResponse
    {
        $session->load(['table', 'rate']);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $session->id,
                'table' => [
                    'id' => $session->table->id,
                    'table_number' => $session->table->table_number,
                ],
                'rate' => [
                    'id' => $session->rate->id,
                    'name' => $session->rate->name,
                    'price_per_hour' => $session->rate->price_per_hour,
                ],
                'start_time' => $session->start_time->toIso8601String(),
                'end_time' => $session->end_time->toIso8601String(),
                'duration_minutes' => $session->duration_minutes,
                'remaining_seconds' => $session->remaining_seconds,
                'total_price' => $session->total_price,
                'status' => $session->status,
                'auto_stop' => $session->auto_stop,
            ],
        ]);
    }
}
