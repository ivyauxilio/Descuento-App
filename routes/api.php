<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Merchant\MenuItemController;
use App\Http\Controllers\Merchant\PromotionController;
use App\Http\Controllers\Merchant\QRCodeController;
use App\Http\Controllers\Merchant\MerchantController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Client\ClientPromotionController;

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

        Route::get('profile', [MerchantController::class, 'profile']);
        Route::get('stats', [MerchantController::class, 'stats']); // Add this route

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

        // Promotions
        Route::get('promotions', [PromotionController::class, 'index']);
        Route::post('promotions', [PromotionController::class, 'store']);
        Route::get('promotions/{promotion}', [PromotionController::class, 'show']);
        Route::put('promotions/{promotion}', [PromotionController::class, 'update']);
        Route::delete('promotions/{promotion}', [PromotionController::class, 'destroy']);
        Route::put('promotions/{promotion}/status', [PromotionController::class, 'updateStatus']);

        // QR Code routes
        Route::post('qr-code/verify', [QRCodeController::class, 'verify']);
        Route::get('qr-code/{promotion}', [QRCodeController::class, 'getQrData']);
        Route::get('qr-code-stats', [QRCodeController::class, 'getStats']);
    });
    
    Route::prefix('client')->name('client.')->group(function () {
        Route::get('/promotions', [ClientPromotionController::class, 'index']);
        Route::get('/promotions/{id}', [ClientPromotionController::class, 'show']); // Add this route
    });
});