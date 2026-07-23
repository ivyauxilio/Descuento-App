<?php

use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MerchantController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\MenuItemController;
use App\Http\Controllers\Admin\AdminPromotionController;

use App\Http\Controllers\Merchant\PromotionController;

use Illuminate\Support\Facades\Route;


// Customer Authentication Routes (if you want customer login)
// Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
// Route::post('/login', [LoginController::class, 'login']);
// Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/login', function () {
    return redirect()->route('admin.login');
})->name('login');

// Root route - Redirect based on authentication
Route::get('/', function () {
    // Check if user is authenticated
    if (Auth::check()) {
        // Check if user is admin
        if (Auth::user()->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }
        // If logged in but not admin, you can redirect to customer dashboard or home
        return redirect('/home');
    }
    
    // Not logged in - redirect to admin login
    return redirect()->route('admin.login');
});

// Admin only routes
// Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
//     Route::get('/admin/dashboard', function () {
//         return response()->json(['message' => 'Admin dashboard']);
//     });
// });

// Merchant and Admin routes
Route::middleware(['auth:sanctum', 'role:admin,merchant'])->group(function () {
    Route::get('/merchant/dashboard', function () {
        return response()->json(['message' => 'Merchant dashboard']);
    });
});

// All authenticated users (customer, merchant, admin)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/dashboard', function () {
        return response()->json(['message' => 'User dashboard']);
    });
});


// Admin Authentication Routes
Route::prefix('admin')->name('admin.')->group(function () {
    // Guest routes (not logged in)
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AdminLoginController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [AdminLoginController::class, 'login']);
        Route::get('/forgot-password', function () {
            return view('auth.admin-forgot-password');
        })->name('password.request');
    });

    // Authenticated routes
    Route::middleware(['auth', 'role:admin'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::post('/logout', [AdminLoginController::class, 'logout'])->name('logout');

            // Merchant CRUD routes
        Route::resource('merchants', MerchantController::class);
        
        // Additional merchant routes
        Route::post('merchants/bulk-delete', [MerchantController::class, 'bulkDelete'])->name('merchants.bulk-delete');
        Route::put('merchants/{merchant}/status', [MerchantController::class, 'updateStatus'])->name('merchants.update-status');
    
            // User Management
        Route::resource('users', UserController::class);
        Route::post('users/bulk-delete', [UserController::class, 'bulkDelete'])->name('users.bulk-delete');
        Route::put('users/{user}/status', [UserController::class, 'updateStatus'])->name('users.update-status');
        Route::put('users/{user}/toggle-verification', [UserController::class, 'toggleVerification'])->name('users.toggle-verification');
    
                // Menu Items
        Route::resource('menu-items', MenuItemController::class);
        Route::post('menu-items/bulk-delete', [MenuItemController::class, 'bulkDelete'])->name('menu-items.bulk-delete');
        Route::put('menu-items/{menu_item}/status', [MenuItemController::class, 'updateStatus'])->name('menu-items.update-status');
        
            // Promotions
        Route::resource('promotions', AdminPromotionController::class);
        Route::post('promotions/bulk-delete', [AdminPromotionController::class, 'bulkDelete'])->name('promotions.bulk-delete');
        Route::put('promotions/{promotion}/status', [AdminPromotionController::class, 'updateStatus'])->name('promotions.update-status');
        Route::get('promotions-stats', [AdminPromotionController::class, 'getStats'])->name('promotions.stats');
        Route::get('promotions-export', [AdminPromotionController::class, 'export'])->name('promotions.export');
    });


});


Route::middleware(['auth', 'role:merchant'])
    ->prefix('merchant')
    ->name('merchant.')
    ->group(function () {
        
        // Dashboard
        Route::get('/dashboard', function () {
            return view('merchant.dashboard');
        })->name('dashboard');

        // Promotions
        Route::get('/promotions', [PromotionController::class, 'index'])->name('promotions.index');
        Route::get('/promotions/{id}', [PromotionController::class, 'show'])->name('promotions.show');
        Route::get('/promotions-stats', [PromotionController::class, 'getStats'])->name('promotions.stats');
        // Add create, store, edit, update, delete routes as needed
    });


    
Route::get('/admin', function () {
    if (Auth::check() && Auth::user()->role === 'admin') {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('admin.login');
});