<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Merchant\MenuItemController;
use App\Http\Controllers\Merchant\PromotionController;
use App\Http\Controllers\Merchant\QRCodeController;
use App\Http\Controllers\Merchant\MerchantController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Client\ClientPromotionController;
use App\Http\Controllers\Merchant\QRScanController;
use App\Http\Controllers\Merchant\CardQRController;
use App\Http\Controllers\Merchant\OrderController;
use App\Http\Controllers\Merchant\ProductController;
use App\Http\Controllers\Client\CardController;
use App\Http\Controllers\Merchant\PlanController;
use App\Http\Controllers\Api\ReferralApiController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ProductController as ClientProductController;
// use App\Http\Controllers\Api\NotificationController as MerchantNotificationController;


// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/webhooks/payment', [PaymentWebhookController::class, 'handle'])
    ->name('webhooks.payment');

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    
    // Add your protected API routes here
    Route::get('/dashboard', function () {
        return response()->json(['message' => 'Welcome to dashboard']);
    });

    Route::get('/referrals/me', [ReferralApiController::class, 'me']);
    Route::get('/referrals/list', [ReferralApiController::class, 'list']);
    Route::get('/wallet', [ReferralApiController::class, 'wallet']);
    Route::get('/wallet/transactions', [ReferralApiController::class, 'walletTransactions']);
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
        Route::delete('promotions/{promotion}/poster', [PromotionController::class, 'deletePoster'])->name('promotions.delete-poster');
        Route::post('/promotions/redeem', [QRScanController::class, 'redeem']);

        Route::post('/promotions/check-credits', [PromotionController::class, 'checkCredits']);
        // Route::post('/scan/redeem', [QRScanController::class, 'redeem']);

        // QR Code routes
        Route::post('qr-code/verify', [QRCodeController::class, 'verify']);
        Route::get('qr-code/{promotion}', [QRCodeController::class, 'getQrData']);
        Route::get('qr-code-stats', [QRCodeController::class, 'getStats']);

        Route::post('/card/scan', [CardQRController::class, 'scan']);
        Route::get('/card/validate', [CardQRController::class, 'validateCard']);
        Route::post('/card/scan-qr', [CardQRController::class, 'getCardByQr']);

        Route::get('/orders', [OrderController::class, 'index']);
        Route::get('/orders/{orderId}', [OrderController::class, 'show']);
        Route::put('/orders/{orderId}/status', [OrderController::class, 'updateStatus']);
        Route::post('/orders', [OrderController::class, 'store']);
        Route::post('/orders/{orderId}/cancel', [OrderController::class, 'cancel']);
        Route::get('/orders/{orderId}/invoice', [OrderController::class, 'generateInvoice']);


        Route::get('/products', [ProductController::class, 'index']);
        Route::get('/products/stats', [ProductController::class, 'stats']);
        Route::get('/products/categories', [ProductController::class, 'categories']);
        Route::post('/products', [ProductController::class, 'store']);
        Route::get('/products/{id}', [ProductController::class, 'show']);
        Route::put('/products/{id}', [ProductController::class, 'update']);
        Route::delete('/products/{id}', [ProductController::class, 'destroy']);
        Route::patch('/products/{id}/toggle-status', [ProductController::class, 'toggleStatus']);
        Route::patch('/products/{id}/toggle-featured', [ProductController::class, 'toggleFeatured']);
        Route::post('/products/{id}/duplicate', [ProductController::class, 'duplicate']);
        Route::post('/products/bulk/stock', [ProductController::class, 'bulkUpdateStock']);
        
        // Discount routes
        Route::post('/products/{productId}/discount', [ProductController::class, 'manageDiscount']);
        
        // Points routes
        Route::post('/products/{productId}/points', [ProductController::class, 'managePoints']);

        // Plans
        Route::get('/plans', [PlanController::class, 'index']);
        Route::get('/plans/{slug}', [PlanController::class, 'show']);
        
        // Wallet
        Route::get('/wallet', [PlanController::class, 'wallet']);
        Route::post('/plans/purchase', [PlanController::class, 'purchase']);
        Route::get('/wallet/transactions', [PlanController::class, 'transactions']);


        Route::prefix('notifications')->group(function () {
            Route::get('/', [NotificationController::class, 'index']);
            Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
            Route::get('/{id}', [NotificationController::class, 'show']);
            Route::post('/{id}/read', [NotificationController::class, 'markAsRead']);
            Route::post('/read-all', [NotificationController::class, 'markAllAsRead']);
            Route::delete('/{id}', [NotificationController::class, 'destroy']);
            Route::delete('/', [NotificationController::class, 'clearAll']);
        });


        // Route::get('/plans', [SubscriptionPlanController::class, 'index']);
        // Route::post('/plans', [SubscriptionPlanController::class, 'store']);
        // Route::get('/plans/{id}', [SubscriptionPlanController::class, 'show']);
        // Route::put('/plans/{id}', [SubscriptionPlanController::class, 'update']);
        // Route::delete('/plans/{id}', [SubscriptionPlanController::class, 'destroy']);
        // Route::patch('/plans/{id}/toggle-status', [SubscriptionPlanController::class, 'toggleStatus']);
        // Route::post('/plans/reorder', [SubscriptionPlanController::class, 'reorder']);

        

    });
    
    Route::prefix('client')->name('client.')->group(function () {
        Route::get('/promotions', [ClientPromotionController::class, 'index']);
        Route::get('/promotions/{id}', [ClientPromotionController::class, 'show']); // Add this route

        Route::get('/cards', [CardController::class, 'index']);
        Route::post('/cards/activate', [CardController::class, 'activate']);
        Route::get('/cards/{id}', [CardController::class, 'show']);

        Route::post('/cards/{id}/lock', [CardController::class, 'toggleLock']);
        Route::post('/cards/{id}/lost', [CardController::class, 'reportLost']);
        Route::get('/cards/{id}/balance', [CardController::class, 'getBalance']);
        Route::get('/cards/{id}/transactions', [CardController::class, 'getTransactions']);

        Route::get('/orders', [OrderController::class, 'customerOrders']);
        Route::get('/orders/{orderId}', [OrderController::class, 'customerOrderDetail']);
        Route::post('/orders', [OrderController::class, 'store']); // Create order from client app
    });

    Route::prefix('products')->group(function () {
        // List + filters
        Route::get('/', [ClientProductController::class, 'index']);

        // Categories for filter chips
        Route::get('/categories', [ClientProductController::class, 'categories']);

        // Featured for home carousel
        Route::get('/featured', [ClientProductController::class, 'featured']);

        // Search (autocomplete)
        Route::get('/search', [ClientProductController::class, 'search']);

        // Single product — MUST be last (wildcard)
        Route::get('/{id}', [ClientProductController::class, 'show']);
    });

    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
        Route::get('/{id}', [NotificationController::class, 'show']);
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead']);
        Route::delete('/{id}', [NotificationController::class, 'destroy']);
        Route::delete('/', [NotificationController::class, 'clearAll']);
    });
});


Route::middleware(['auth:sanctum', 'merchant'])->prefix('merchant')->group(function () {



});