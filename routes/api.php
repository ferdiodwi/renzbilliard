<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\RateController;
use App\Http\Controllers\Api\SessionController;
use App\Http\Controllers\Api\TableController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PosController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Dashboard (all authenticated users)
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
    Route::get('/dashboard/chart', [DashboardController::class, 'chart']);
    Route::get('/alerts/check', [DashboardController::class, 'alerts']);
    
    // Reports
    Route::get('/reports/export', [\App\Http\Controllers\Api\ReportController::class, 'export']);
    Route::get('/reports', [\App\Http\Controllers\Api\ReportController::class, 'index']);

    // Tables (read for all, write for those with permissions)
    Route::get('/tables', [TableController::class, 'index']);
    Route::get('/tables/{table}', [TableController::class, 'show']);
    Route::middleware('permission:create-tables')->post('/tables', [TableController::class, 'store']);
    Route::middleware('permission:edit-tables')->put('/tables/{table}', [TableController::class, 'update']);
    Route::middleware('permission:delete-tables')->delete('/tables/{table}', [TableController::class, 'destroy']);

    // Rates (read for all, write for those with permissions)
    Route::get('/rates', [RateController::class, 'index']);
    Route::get('/rates/{rate}', [RateController::class, 'show']);
    Route::middleware('permission:create-rates')->post('/rates', [RateController::class, 'store']);
    Route::middleware('permission:edit-rates')->put('/rates/{rate}', [RateController::class, 'update']);
    Route::middleware('permission:delete-rates')->delete('/rates/{rate}', [RateController::class, 'destroy']);

    // Sessions (all authenticated users can manage)
    Route::get('/sessions/active', [SessionController::class, 'active']);
    Route::get('/sessions/{session}', [SessionController::class, 'show']);
    Route::post('/sessions/start', [SessionController::class, 'start']);
    Route::post('/sessions/{session}/stop', [SessionController::class, 'stop']);
    Route::post('/sessions/{session}/extend', [SessionController::class, 'extend']);
    Route::delete('/sessions/{session}', [SessionController::class, 'destroy']);
    
    // Bookings (all authenticated users can manage)
    Route::get('/bookings/check-availability', [BookingController::class, 'checkAvailability']);
    Route::get('/bookings', [BookingController::class, 'index']);
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::get('/bookings/{id}', [BookingController::class, 'show']);
    Route::put('/bookings/{id}', [BookingController::class, 'update']);
    Route::delete('/bookings/{id}', [BookingController::class, 'destroy']);
    Route::post('/bookings/{id}/check-in', [BookingController::class, 'checkIn']);
    
    // Session Orders
    Route::get('/sessions/{session}/order', [\App\Http\Controllers\Api\OrderController::class, 'show']);
    Route::post('/sessions/{session}/order', [\App\Http\Controllers\Api\OrderController::class, 'store']);
    Route::delete('/sessions/{session}/order/items/{itemId}', [\App\Http\Controllers\Api\OrderController::class, 'destroyItem']);

    // Transactions (all authenticated users)
    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::get('/transactions/unpaid-sessions', [TransactionController::class, 'unpaidSessions']);
    Route::get('/transactions/{transaction}', [TransactionController::class, 'show']);
    Route::post('/transactions', [TransactionController::class, 'store']);
    Route::get('/transactions/{transaction}/invoice', [TransactionController::class, 'invoice']);

    // Profile (all authenticated users)
    Route::put('/profile', [ProfileController::class, 'update']);

    // Products Management (permission-based)
    Route::middleware('permission:view-products')->get('/products', [ProductController::class, 'index']);
    Route::get('/products/{id}', [ProductController::class, 'show']); // Keep existing show route
    Route::middleware('permission:create-products')->post('/products', [ProductController::class, 'store']);
    Route::middleware('permission:edit-products')->put('/products/{id}', [ProductController::class, 'update']);
    Route::middleware('permission:delete-products')->delete('/products/{id}', [ProductController::class, 'destroy']);

    // Categories Management (admin only)
    Route::apiResource('categories', \App\Http\Controllers\Api\CategoryController::class);

    // POS (all authenticated users)
    Route::prefix('pos')->group(function () {
        Route::get('/products', [PosController::class, 'products']);
        Route::post('/orders', [PosController::class, 'createOrder']);
        Route::get('/orders', [PosController::class, 'orders']);
        Route::get('/orders/{id}', [PosController::class, 'showOrder']);
        Route::post('/orders/{id}/pay', [PosController::class, 'payOrder']);
    });

    // General Order Management
    Route::delete('/orders/{id}', [\App\Http\Controllers\Api\OrderController::class, 'destroy']);

    // Expenses (all authenticated users)
    Route::get('/expenses', [\App\Http\Controllers\Api\ExpenseController::class, 'index']);
    Route::post('/expenses', [\App\Http\Controllers\Api\ExpenseController::class, 'store']);
    Route::get('/expenses/{expense}', [\App\Http\Controllers\Api\ExpenseController::class, 'show']);
    Route::put('/expenses/{expense}', [\App\Http\Controllers\Api\ExpenseController::class, 'update']);
    Route::delete('/expenses/{expense}', [\App\Http\Controllers\Api\ExpenseController::class, 'destroy']);

    // Combined Income (billiard + F&B)
    Route::get('/income', [\App\Http\Controllers\Api\IncomeController::class, 'index']);
    
    // Admin Delete Transaction
    Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy']);

    // User Management (permission-based)
    Route::middleware('permission:view-users')->get('/users', [UserController::class, 'index']);
    Route::middleware('permission:create-users')->post('/users', [UserController::class, 'store']);
    Route::middleware('permission:edit-users')->put('/users/{id}', [UserController::class, 'update']);
    Route::middleware('permission:delete-users')->delete('/users/{id}', [UserController::class, 'destroy']);
});
