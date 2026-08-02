<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\InventoryTransaction;
use App\Models\MenuItem;
use App\Models\Merchant;
use App\Models\Promotion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard with real data.
     */
    public function index()
    {
        // ============================================
        // BASIC STATISTICS
        // ============================================
        $stats = [
            'total_users' => User::count(),
            'total_merchants' => Merchant::count(),
            'active_merchants' => Merchant::where('status', 'active')->count(),
            'total_menu_items' => MenuItem::count(),
            'total_promotions' => Promotion::count(),
            'active_promotions' => Promotion::where('status', 'active')
                ->whereDate('start_date', '<=', now())
                ->where(function ($q) {
                    $q->whereNull('end_date')
                      ->orWhereDate('end_date', '>=', now());
                })
                ->count(),
            'total_categories' => Category::count(),
        ];

        // ============================================
        // INVENTORY STATISTICS
        // ============================================
        $inventoryStats = [
            'total_items' => MenuItem::sum('stock_quantity'),
            'in_stock' => MenuItem::where('stock_status', 'in_stock')->count(),
            'low_stock' => MenuItem::where('stock_status', 'low_stock')->count(),
            'out_of_stock' => MenuItem::where('stock_status', 'out_of_stock')->count(),
            'total_value' => MenuItem::sum(DB::raw('price * stock_quantity')),
        ];

        // ============================================
        // RECENT ACTIVITIES
        // ============================================
        
        // Recent merchants
        $recentMerchants = Merchant::with('owner')
            ->latest()
            ->take(5)
            ->get();

        // Recent users
        $recentUsers = User::latest()
            ->take(5)
            ->get();

        // Recent promotions
        $recentPromotions = Promotion::with('merchant')
            ->latest()
            ->take(5)
            ->get();

        // Recent inventory transactions
        $recentTransactions = InventoryTransaction::with(['menuItem', 'performedBy'])
            ->latest()
            ->take(10)
            ->get();

        // ============================================
        // CHART DATA
        // ============================================
        
        // Monthly new users (last 12 months)
        $monthlyUsers = User::select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get()
            ->map(function ($item) {
                $item->month_name = date('M', mktime(0, 0, 0, $item->month, 1));
                return $item;
            });

        // Monthly new merchants (last 12 months)
        $monthlyMerchants = Merchant::select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get()
            ->map(function ($item) {
                $item->month_name = date('M', mktime(0, 0, 0, $item->month, 1));
                return $item;
            });

        // User role distribution
        $roleDistribution = User::select('role', DB::raw('COUNT(*) as count'))
            ->groupBy('role')
            ->get();

        // Merchant status distribution
        $merchantStatusDistribution = Merchant::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();

        // Promotion type distribution
        $promoTypeDistribution = Promotion::select('promo_type', DB::raw('COUNT(*) as count'))
            ->groupBy('promo_type')
            ->get();

        // Stock status distribution
        $stockStatusDistribution = MenuItem::select('stock_status', DB::raw('COUNT(*) as count'))
            ->groupBy('stock_status')
            ->get();

        // ============================================
        // TOP PERFORMERS
        // ============================================
        
        // Top merchants by menu items
        $topMerchants = Merchant::withCount('menuItems')
            ->orderBy('menu_items_count', 'desc')
            ->take(5)
            ->get();

        // Top merchants by promotions
        $topPromoMerchants = Merchant::withCount('promotions')
            ->orderBy('promotions_count', 'desc')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'stats',
            'inventoryStats',
            'recentMerchants',
            'recentUsers',
            'recentPromotions',
            'recentTransactions',
            'monthlyUsers',
            'monthlyMerchants',
            'roleDistribution',
            'merchantStatusDistribution',
            'promoTypeDistribution',
            'stockStatusDistribution',
            'topMerchants',
            'topPromoMerchants'
        ));
    }
}