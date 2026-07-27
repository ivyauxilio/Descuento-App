<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryTransaction;
use App\Models\MenuItem;
use App\Models\Merchant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminInventoryController extends Controller
{
    /**
     * Main inventory dashboard showing all merchants' inventory.
     */
    public function index(Request $request)
    {
        // Query with relationships
        $query = MenuItem::with(['merchant', 'merchant.owner']);

        // Apply filters
        $this->applyFilters($query, $request);

        // Apply sorting
        $this->applySorting($query, $request);

        // Paginate results
        $menuItems = $query->paginate(20);

        // Get statistics
        $stats = $this->getStats();

        // Get merchants for filter dropdown
        $merchants = Merchant::where('status', 'active')
            ->orderBy('business_name')
            ->get(['merchant_id', 'business_name']);

        $categories = $this->getCategories();
        $stockStatuses = ['in_stock', 'low_stock', 'out_of_stock'];

        return view('admin.inventory.index', compact(
            'menuItems',
            'stats',
            'merchants',
            'categories',
            'stockStatuses'
        ));
    }

    /**
     * View inventory for a specific merchant.
     */
    public function merchantInventory(string $merchantId)
    {
        $merchant = Merchant::with(['owner', 'menuItems'])
            ->findOrFail($merchantId);

        $menuItems = $merchant->menuItems()
            ->orderBy('name')
            ->get();

        $stats = [
            'total' => $menuItems->count(),
            'in_stock' => $menuItems->where('stock_status', 'in_stock')->count(),
            'low_stock' => $menuItems->where('stock_status', 'low_stock')->count(),
            'out_of_stock' => $menuItems->where('stock_status', 'out_of_stock')->count(),
            'total_value' => $menuItems->sum(function ($item) {
                return $item->price * $item->stock_quantity;
            }),
        ];

        return view('admin.inventory.merchant', compact('merchant', 'menuItems', 'stats'));
    }

    /**
     * View details of a specific menu item's inventory.
     */
    public function show(string $id)
    {
        $menuItem = MenuItem::with(['merchant', 'merchant.owner'])
            ->findOrFail($id);

        $transactions = $menuItem->transactions()
            ->with('performedBy')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        $stats = [
            'total_in' => $menuItem->transactions()->where('type', 'stock_in')->sum('quantity'),
            'total_out' => $menuItem->transactions()->where('type', 'stock_out')->sum('quantity'),
            'total_adjustments' => $menuItem->transactions()->where('type', 'adjustment')->count(),
        ];

        return view('admin.inventory.show', compact('menuItem', 'transactions', 'stats'));
    }

    /**
     * View all low stock items across merchants.
     */
    public function lowStock()
    {
        $lowStockItems = MenuItem::with(['merchant'])
            ->where('stock_quantity', '<=', DB::raw('low_stock_threshold'))
            ->where('stock_quantity', '>', 0)
            ->orderBy('stock_quantity')
            ->get();

        $outOfStockItems = MenuItem::with(['merchant'])
            ->where('stock_quantity', 0)
            ->orderBy('name')
            ->get();

        $stats = [
            'low_stock_count' => $lowStockItems->count(),
            'out_of_stock_count' => $outOfStockItems->count(),
        ];

        return view('admin.inventory.low-stock', compact(
            'lowStockItems',
            'outOfStockItems',
            'stats'
        ));
    }

    /**
     * Apply filters to the query.
     */
    private function applyFilters($query, $request)
    {
        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhereHas('merchant', function ($q2) use ($search) {
                      $q2->where('business_name', 'like', "%{$search}%");
                  });
            });
        }

        // Merchant filter
        if ($request->filled('merchant_id')) {
            $query->where('merchant_id', $request->merchant_id);
        }

        // Stock status filter
        if ($request->filled('stock_status')) {
            $query->where('stock_status', $request->stock_status);
        }

        // Category filter
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
    }

    /**
     * Apply sorting to the query.
     */
    private function applySorting($query, $request)
    {
        $sortField = $request->get('sort', 'stock_quantity');
        $sortDirection = $request->get('direction', 'asc');
        $query->orderBy($sortField, $sortDirection);
    }

    /**
     * Get inventory statistics.
     */
    private function getStats()
    {
        return [
            'total' => MenuItem::count(),
            'in_stock' => MenuItem::where('stock_status', 'in_stock')->count(),
            'low_stock' => MenuItem::where('stock_status', 'low_stock')->count(),
            'out_of_stock' => MenuItem::where('stock_status', 'out_of_stock')->count(),
            'total_value' => MenuItem::sum(DB::raw('price * stock_quantity')),
            'merchants_with_stock' => MenuItem::distinct('merchant_id')->count('merchant_id'),
        ];
    }

    /**
     * Get categories list.
     */
    private function getCategories(): array
    {
        return [
            'appetizer' => 'Appetizer',
            'main_course' => 'Main Course',
            'dessert' => 'Dessert',
            'beverage' => 'Beverage',
            'soup' => 'Soup',
            'salad' => 'Salad',
            'snack' => 'Snack',
            'combo' => 'Combo Meal',
            'family' => 'Family Meal',
            'side' => 'Side Dish',
        ];
    }
}