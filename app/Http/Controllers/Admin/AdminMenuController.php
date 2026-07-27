<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Merchant;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminMenuController extends Controller
{
    /**
     * Display merchant menu with inventory.
     */
    public function index(Request $request, string $merchantId)
    {
        $merchant = Merchant::with(['owner', 'category', 'province'])
            ->findOrFail($merchantId);

        $query = MenuItem::where('merchant_id', $merchantId);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        // Category filter
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Stock status filter
        if ($request->filled('stock_status')) {
            $query->where('stock_status', $request->stock_status);
        }

        // Sort
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $menuItems = $query->paginate(12);

        // Get inventory statistics
        $stats = [
            'total' => MenuItem::where('merchant_id', $merchantId)->count(),
            'available' => MenuItem::where('merchant_id', $merchantId)
                ->where('status', 'available')
                ->count(),
            'unavailable' => MenuItem::where('merchant_id', $merchantId)
                ->where('status', 'unavailable')
                ->count(),
            'in_stock' => MenuItem::where('merchant_id', $merchantId)
                ->where('stock_quantity', '>', 0)
                ->count(),
            'low_stock' => MenuItem::where('merchant_id', $merchantId)
                ->where('stock_quantity', '<=', DB::raw('low_stock_threshold'))
                ->where('stock_quantity', '>', 0)
                ->count(),
            'out_of_stock' => MenuItem::where('merchant_id', $merchantId)
                ->where('stock_quantity', 0)
                ->count(),
            'total_value' => MenuItem::where('merchant_id', $merchantId)
                ->sum(DB::raw('price * stock_quantity')),
        ];

        $categories = $this->getCategories();
        $stockStatuses = ['in_stock', 'low_stock', 'out_of_stock'];

        return view('admin.merchants.menu', compact(
            'merchant',
            'menuItems',
            'stats',
            'categories',
            'stockStatuses'
        ));
    }

    /**
     * Show a specific menu item.
     */
    public function show(string $merchantId, string $menuItemId)
    {
        $merchant = Merchant::findOrFail($merchantId);
        $menuItem = MenuItem::where('merchant_id', $merchantId)
            ->findOrFail($menuItemId);

        $transactions = $menuItem->transactions()
            ->with('performedBy')
            ->orderBy('created_at', 'desc')
            ->limit(30)
            ->get();

        $stats = [
            'total_in' => $menuItem->transactions()->where('type', 'stock_in')->sum('quantity'),
            'total_out' => $menuItem->transactions()->where('type', 'stock_out')->sum('quantity'),
            'total_adjustments' => $menuItem->transactions()->where('type', 'adjustment')->count(),
        ];

        $categories = $this->getCategories();

        return view('admin.merchants.menu-show', compact(
            'merchant',
            'menuItem',
            'transactions',
            'stats',
            'categories'
        ));
    }

    /**
     * Add stock to menu item (Admin).
     */
    public function addStock(Request $request, string $merchantId, string $menuItemId)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
            'reason' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $menuItem = MenuItem::where('merchant_id', $merchantId)
                ->findOrFail($menuItemId);

            $menuItem->addStock(
                $request->quantity,
                $request->reason ?? 'Admin added stock',
                'admin_add',
                null
            );

            DB::commit();

            return redirect()
                ->route('admin.merchants.menu', $merchantId)
                ->with('success', "Added {$request->quantity} {$menuItem->unit}(s) to {$menuItem->name}!");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to add stock: ' . $e->getMessage());
        }
    }

    /**
     * Remove stock from menu item (Admin).
     */
    public function removeStock(Request $request, string $merchantId, string $menuItemId)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
            'reason' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $menuItem = MenuItem::where('merchant_id', $merchantId)
                ->findOrFail($menuItemId);

            if ($menuItem->stock_quantity < $request->quantity) {
                return back()->with('error', 'Not enough stock available!');
            }

            $menuItem->removeStock(
                $request->quantity,
                $request->reason ?? 'Admin removed stock',
                'admin_remove',
                null
            );

            DB::commit();

            return redirect()
                ->route('admin.merchants.menu', $merchantId)
                ->with('success', "Removed {$request->quantity} {$menuItem->unit}(s) from {$menuItem->name}!");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to remove stock: ' . $e->getMessage());
        }
    }

    /**
     * Adjust stock (Admin).
     */
    public function adjustStock(Request $request, string $merchantId, string $menuItemId)
    {
        $request->validate([
            'quantity' => 'required|integer|min:0',
            'reason' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $menuItem = MenuItem::where('merchant_id', $merchantId)
                ->findOrFail($menuItemId);

            $previous = $menuItem->stock_quantity;
            $diff = $request->quantity - $previous;

            if ($diff > 0) {
                $menuItem->addStock(
                    $diff,
                    $request->reason ?? 'Admin adjustment',
                    'admin_adjust',
                    null
                );
            } elseif ($diff < 0) {
                $menuItem->removeStock(
                    abs($diff),
                    $request->reason ?? 'Admin adjustment',
                    'admin_adjust',
                    null
                );
            } else {
                return back()->with('info', 'No changes made.');
            }

            DB::commit();

            return redirect()
                ->route('admin.merchants.menu', $merchantId)
                ->with('success', "Stock adjusted to {$request->quantity} {$menuItem->unit}(s)!");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to adjust stock: ' . $e->getMessage());
        }
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