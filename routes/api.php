<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Merchant\MenuItemController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    
    // Add your protected API routes here
    Route::get('/dashboard', function () {
        return response()->json(['message' => 'Welcome to dashboard']);
    });
});

// Merchant routes (protected by auth:api)
Route::middleware('auth:api')->group(function () {

    // ============================================
    // MERCHANT MENU ITEMS
    // ============================================
    Route::prefix('merchant')->name('merchant.')->group(function () {

        // Menu Items
        Route::get('menu-items', [MenuItemController::class, 'index']);
        Route::post('menu-items', [MenuItemController::class, 'store']);
        Route::get('menu-items/categories', [MenuItemController::class, 'categories']);
        Route::get('menu-items/low-stock', [MenuItemController::class, 'lowStock']);
        Route::get('menu-items/{menu_item}', [MenuItemController::class, 'show']);
        Route::put('menu-items/{menu_item}', [MenuItemController::class, 'update']);
        Route::delete('menu-items/{menu_item}', [MenuItemController::class, 'destroy']);
        
        // Stock Management
        Route::post('menu-items/{menu_item}/add-stock', [MenuItemController::class, 'addStock']);
        Route::post('menu-items/{menu_item}/remove-stock', [MenuItemController::class, 'removeStock']);
        
        // Status Management
        Route::put('menu-items/{menu_item}/status', [MenuItemController::class, 'updateStatus']);
    });
});