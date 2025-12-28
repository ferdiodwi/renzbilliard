<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RateController extends Controller
{
    /**
     * Display a listing of rates.
     */
    public function index(): JsonResponse
    {
        if (request()->has('all')) {
            $rates = Rate::orderBy('name')->get();
        } else {
            $rates = Rate::orderBy('name')->paginate(10);
        }

        return response()->json([
            'success' => true,
            'data' => $rates,
        ]);
    }

    /**
     * Store a newly created rate.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:50',
            'price_per_hour' => 'required|numeric|min:0',
        ]);

        $rate = Rate::create([
            'name' => $request->name,
            'price_per_hour' => $request->price_per_hour,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tarif berhasil ditambahkan',
            'data' => $rate,
        ], 201);
    }

    /**
     * Display the specified rate.
     */
    public function show(Rate $rate): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $rate,
        ]);
    }

    /**
     * Update the specified rate.
     */
    public function update(Request $request, Rate $rate): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|string|max:50',
            'price_per_hour' => 'sometimes|numeric|min:0',
        ]);

        $rate->update($request->only(['name', 'price_per_hour']));

        return response()->json([
            'success' => true,
            'message' => 'Tarif berhasil diupdate',
            'data' => $rate,
        ]);
    }

    /**
     * Remove the specified rate.
     */
    public function destroy(Rate $rate): JsonResponse
    {
        // Check if rate is used in any session
        if ($rate->sessions()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat menghapus tarif yang sudah digunakan',
            ], 422);
        }

        $rate->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tarif berhasil dihapus',
        ]);
    }
}
