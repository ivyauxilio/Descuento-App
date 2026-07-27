<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Merchant;
use App\Models\MenuItem;
use Illuminate\Http\Request;

class AdminMenuItemController extends Controller
{
    /**
     * Display menu items for a specific merchant.
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
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        // Category filter
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Sort
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $menuItems = $query->paginate(15);

        // Get statistics
        $stats = [
            'total' => MenuItem::where('merchant_id', $merchantId)->count(),
            'available' => MenuItem::where('merchant_id', $merchantId)
                ->where('status', 'available')
                ->count(),
            'unavailable' => MenuItem::where('merchant_id', $merchantId)
                ->where('status', 'unavailable')
                ->count(),
            'out_of_stock' => MenuItem::where('merchant_id', $merchantId)
                ->where('status', 'out_of_stock')
                ->count(),
        ];

        $categories = $this->getCategories();
        $statuses = ['available', 'unavailable', 'out_of_stock'];

        return view('admin.merchants.menu', compact(
            'merchant', 
            'menuItems', 
            'stats', 
            'categories', 
            'statuses'
        ));
    }

    /**
     * Display a specific menu item.
     */
    public function show(string $merchantId, string $menuItemId)
    {
        $merchant = Merchant::findOrFail($merchantId);
        $menuItem = MenuItem::where('merchant_id', $merchantId)
            ->findOrFail($menuItemId);

        $categories = $this->getCategories();

        return view('admin.merchants.menu-show', compact('merchant', 'menuItem', 'categories'));
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