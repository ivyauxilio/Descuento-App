<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MenuItemRequest;
use App\Models\InventoryTransaction;
use App\Models\MenuItem;
use App\Models\Merchant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MenuItemController extends Controller
{
    /**
     * Display a listing of menu items.
     */
    public function index(Request $request)
    {
        $query = MenuItem::with('merchant');

        // Search filter
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Merchant filter
        if ($request->filled('merchant_id')) {
            $query->where('merchant_id', $request->merchant_id);
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
        $merchants = Merchant::orderBy('business_name')->get();
        $statuses = ['available', 'unavailable', 'out_of_stock'];

        return view('admin.menu-items.index', compact('menuItems', 'merchants', 'statuses'));
    }

    /**
     * Show the form for creating a new menu item.
     */
    public function create()
    {
        $merchants = Merchant::orderBy('business_name')->get();
        // $merchant = Merchant::findOrFail($merchantId);
        // $statuses = ['available', 'unavailable', 'out_of_stock'];
        $statuses = ['in_stock', 'low_stock', 'out_of_stock'];

        return view('admin.menu-items.create', compact('merchants', 'statuses'));
    }

    /**
     * Store a newly created menu item.
     */
    public function store(MenuItemRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();

            $data = $request->all();
            $data['merchant_id'] = $merchantId;
            $data['stock_quantity'] = $request->stock_quantity ?? 0;
            $data['low_stock_threshold'] = $request->low_stock_threshold ?? 5;
            $data['unit'] = $request->unit ?? 'piece';
            
            // Handle image upload
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('menu-items', 'public');
                $data['image_url'] = $imagePath;
            }

            $menuItem = MenuItem::create($data);

            DB::commit();

            return redirect()
                ->route('admin.merchants.menu', $merchantId)
                // ->route('admin.menu-items.index')
                ->with('success', 'Menu item created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Failed to create menu item: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified menu item.
     */
    public function show(string $id)
    {
        $menuItem = MenuItem::with(['merchant', 'promotionsAsFree', 'promotionsAsRequired'])
            ->findOrFail($id);
        return view('admin.menu-items.show', compact('menuItem'));
    }

    /**
     * Show the form for editing the specified menu item.
     */
    public function edit(string $id)
    {
        $menuItem = MenuItem::findOrFail($id);
        $merchants = Merchant::orderBy('business_name')->get();
        $statuses = ['available', 'unavailable', 'out_of_stock'];

        return view('admin.menu-items.edit', compact('menuItem', 'merchants', 'statuses'));
    }

    /**
     * Update the specified menu item.
     */
    public function update(MenuItemRequest $request, string $id)
    {
        try {
            DB::beginTransaction();

            $menuItem = MenuItem::where('merchant_id', $merchantId)
                ->findOrFail($menuItemId);

            $data = $request->all();

            // Handle image upload
            if ($request->hasFile('image')) {
                if ($menuItem->image_url && Storage::disk('public')->exists($menuItem->image_url)) {
                    Storage::disk('public')->delete($menuItem->image_url);
                }
                $imagePath = $request->file('image')->store('menu-items', 'public');
                $data['image_url'] = $imagePath;
            }

            $menuItem->update($data);

            DB::commit();

            return redirect()
                ->route('admin.merchants.menu', $merchantId)
                ->with('success', 'Menu item updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Failed to update menu item: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified menu item.
     */
    public function destroy(string $id)
    {
        try {
            $menuItem = MenuItem::findOrFail($id);
            
            // Delete image
            if ($menuItem->image_url) {
                Storage::disk('public')->delete($menuItem->image_url);
            }
            
            $menuItem->delete();

            return redirect()
                ->route('admin.merchants.menu', $merchantId)
                ->with('success', 'Menu item deleted successfully!');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete menu item: ' . $e->getMessage());
        }
    }

    /**
     * Update menu item status.
     */
    public function updateStatus(Request $request, string $id)
    {
        $request->validate([
            'status' => ['required', 'in:available,unavailable,out_of_stock'],
        ]);

        try {
            $menuItem = MenuItem::findOrFail($id);
            $menuItem->status = $request->status;
            $menuItem->save();

            return redirect()
                ->route('admin.menu-items.index')
                ->with('success', 'Menu item status updated successfully!');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update status: ' . $e->getMessage());
        }
    }

    /**
     * Bulk delete menu items.
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:menu_items,menu_item_id'
        ]);

        try {
            // Delete images
            $items = MenuItem::whereIn('menu_item_id', $request->ids)->get();
            foreach ($items as $item) {
                if ($item->image_url) {
                    Storage::disk('public')->delete($item->image_url);
                }
            }

            MenuItem::whereIn('menu_item_id', $request->ids)->delete();

            return redirect()
                ->route('admin.menu-items.index')
                ->with('success', count($request->ids) . ' menu items deleted successfully!');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete menu items: ' . $e->getMessage());
        }
    }

    public function merchantTransactions(string $menuItemId)
    {
        $menuItem = MenuItem::with('merchant')
            ->findOrFail($menuItemId);

        $transactions = $menuItem->transactions()
            ->with('performedBy')
            ->orderBy('created_at', 'desc')
            ->paginate(30);

        $stats = [
            'total_in' => $menuItem->transactions()->where('type', 'stock_in')->sum('quantity'),
            'total_out' => $menuItem->transactions()->where('type', 'stock_out')->sum('quantity'),
            'total_adjustments' => $menuItem->transactions()->where('type', 'adjustment')->count(),
        ];

        return view('admin.inventory.transactions', compact('menuItem', 'transactions', 'stats'));
    }


        /**
     * Show inventory for a specific menu item.
     */
    public function inventory(string $id)
    {
        $merchant = auth()->user()->merchant;
        
        if (!$merchant) {
            return redirect()->route('merchant.dashboard')
                ->with('error', 'You are not associated with any merchant.');
        }

        $menuItem = MenuItem::where('merchant_id', $merchant->merchant_id)
            ->findOrFail($id);

        $transactions = $menuItem->transactions()
            ->with('performedBy')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('merchant.menu-items.inventory', compact('menuItem', 'transactions'));
    }

    /**
     * Update inventory (add stock).
     */
    public function addStock(Request $request, string $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
            'reason' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $merchant = auth()->user()->merchant;
            $menuItem = MenuItem::where('merchant_id', $merchant->merchant_id)
                ->findOrFail($id);

            $menuItem->addStock(
                $request->quantity,
                $request->reason ?? 'Manual stock addition',
                'manual',
                null
            );

            DB::commit();

            return redirect()
                ->route('merchant.menu-items.inventory', $menuItem->menu_item_id)
                ->with('success', "Added {$request->quantity} {$menuItem->unit}(s) to stock!");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to add stock: ' . $e->getMessage());
        }
    }

    /**
     * Update inventory (remove stock).
     */
    public function removeStock(Request $request, string $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
            'reason' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $merchant = auth()->user()->merchant;
            $menuItem = MenuItem::where('merchant_id', $merchant->merchant_id)
                ->findOrFail($id);

            if ($menuItem->stock_quantity < $request->quantity) {
                return back()->with('error', 'Not enough stock available!');
            }

            $menuItem->removeStock(
                $request->quantity,
                $request->reason ?? 'Manual stock removal',
                'manual',
                null
            );

            DB::commit();

            return redirect()
                ->route('merchant.menu-items.inventory', $menuItem->menu_item_id)
                ->with('success', "Removed {$request->quantity} {$menuItem->unit}(s) from stock!");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to remove stock: ' . $e->getMessage());
        }
    }

    /**
     * Adjust inventory (set exact quantity).
     */
    public function adjustStock(Request $request, string $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:0',
            'reason' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $merchant = auth()->user()->merchant;
            $menuItem = MenuItem::where('merchant_id', $merchant->merchant_id)
                ->findOrFail($id);

            $previous = $menuItem->stock_quantity;
            $diff = $request->quantity - $previous;

            if ($diff > 0) {
                $menuItem->addStock($diff, $request->reason ?? 'Stock adjustment', 'adjustment', null);
            } elseif ($diff < 0) {
                $menuItem->removeStock(abs($diff), $request->reason ?? 'Stock adjustment', 'adjustment', null);
            } else {
                return back()->with('info', 'No changes made.');
            }

            DB::commit();

            return redirect()
                ->route('merchant.menu-items.inventory', $menuItem->menu_item_id)
                ->with('success', "Stock adjusted to {$request->quantity} {$menuItem->unit}(s)!");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to adjust stock: ' . $e->getMessage());
        }
    }

    /**
     * Get low stock items.
     */
    public function lowStock()
    {
        $merchant = auth()->user()->merchant;
        
        if (!$merchant) {
            return redirect()->route('merchant.dashboard')
                ->with('error', 'You are not associated with any merchant.');
        }

        $lowStockItems = MenuItem::where('merchant_id', $merchant->merchant_id)
            ->where('stock_quantity', '<=', DB::raw('low_stock_threshold'))
            ->where('stock_quantity', '>', 0)
            ->orderBy('stock_quantity')
            ->get();

        $outOfStockItems = MenuItem::where('merchant_id', $merchant->merchant_id)
            ->where('stock_quantity', 0)
            ->orderBy('name')
            ->get();

        return view('merchant.menu-items.low-stock', compact('lowStockItems', 'outOfStockItems'));
    }

        /**
     * Display merchant menu with inventory.
     */
    public function merchantMenu(Request $request, string $merchantId)
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
                //   ->orWhere('category', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        // Category filter
        // if ($request->filled('category')) {
        //     $query->where('category', $request->category);
        // }

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

        // $categories = $this->getCategories();
        $stockStatuses = ['in_stock', 'low_stock', 'out_of_stock'];

        return view('admin.merchants.menu', compact(
            'merchant',
            'menuItems',
            'stats',
            'stockStatuses'
        ));
    }

    /**
     * Show a specific menu item with inventory details.
     */
    public function merchantMenuShow(string $merchantId, string $menuItemId)
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

        // $categories = $this->getCategories();

        return view('admin.merchants.menu-show', compact(
            'merchant',
            'menuItem',
            'transactions',
            'stats'
        ));
    }

    /**
     * Show form to create a new menu item for a merchant.
     */
    public function merchantMenuCreate(string $merchantId)
    {
        $merchant = Merchant::findOrFail($merchantId);
        $statuses = ['in_stock', 'low_stock', 'out_of_stock'];

        return view('admin.merchants.menu.create', compact('merchant', 'statuses'));
    }

        /**
     * Show form to edit a menu item.
     */
    public function merchantMenuEdit(string $merchantId, string $menuItemId)
    {
        $merchant = Merchant::findOrFail($merchantId);
        $menuItem = MenuItem::where('merchant_id', $merchantId)
            ->findOrFail($menuItemId);

        // $categories = $this->getCategories();
       $statuses = ['in_stock', 'low_stock', 'out_of_stock'];

        return view('admin.merchants.menu.edit', compact(
            'merchant',
            'menuItem',
            'statuses'
        ));
    }
    /**
     * Store a new menu item for a merchant.
     */
    public function merchantMenuStore(Request $request, string $merchantId)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'price' => 'required|numeric|min:0|max:999999.99',
            'stock_status' => 'required|in:in_stock,low_stock,out_of_stock',
            'stock_quantity' => 'nullable|integer|min:0',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'unit' => 'nullable|string|max:50',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        try {
            DB::beginTransaction();

            $data = $request->all();
            $data['merchant_id'] = $merchantId;
            $data['stock_quantity'] = $request->stock_quantity ?? 0;
            $data['low_stock_threshold'] = $request->low_stock_threshold ?? 5;
            $data['unit'] = $request->unit ?? 'piece';

            // Handle image upload
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('menu-items', 'public');
                $data['image_url'] = $imagePath;
            }

            $menuItem = MenuItem::create($data);

            // Log initial stock
            if ($data['stock_quantity'] > 0) {
                $menuItem->addStock(
                    $data['stock_quantity'],
                    'Initial stock from admin',
                    'initial_stock',
                    null
                );
            }

            DB::commit();

            return redirect()
                ->route('admin.merchants.menu', $merchantId)
                ->with('success', 'Menu item created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Failed to create menu item: ' . $e->getMessage());
        }
    }


        /**
     * Update a menu item.
     */
    public function merchantMenuUpdate(Request $request, string $merchantId, string $menuItemId)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'price' => 'required|numeric|min:0|max:999999.99',
            'stock_status' => 'required|in:in_stock,low_stock,out_of_stock',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'unit' => 'nullable|string|max:50',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        try {
            DB::beginTransaction();

            $menuItem = MenuItem::where('merchant_id', $merchantId)
                ->findOrFail($menuItemId);

            $data = $request->all();

            // Handle image upload
            if ($request->hasFile('image')) {
                if ($menuItem->image_url && Storage::disk('public')->exists($menuItem->image_url)) {
                    Storage::disk('public')->delete($menuItem->image_url);
                }
                $imagePath = $request->file('image')->store('menu-items', 'public');
                $data['image_url'] = $imagePath;
            }

            $menuItem->update($data);

            DB::commit();

            return redirect()
                ->route('admin.merchants.menu', $merchantId)
                ->with('success', 'Menu item updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Failed to update menu item: ' . $e->getMessage());
        }
    }

        /**
     * Update image only.
     */
    public function merchantMenuUpdateImage(Request $request, string $merchantId, string $menuItemId)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        try {
            DB::beginTransaction();

            $menuItem = MenuItem::where('merchant_id', $merchantId)
                ->findOrFail($menuItemId);

            if ($menuItem->image_url && Storage::disk('public')->exists($menuItem->image_url)) {
                Storage::disk('public')->delete($menuItem->image_url);
            }

            $imagePath = $request->file('image')->store('menu-items', 'public');
            $menuItem->image_url = $imagePath;
            $menuItem->save();

            DB::commit();

            return redirect()
                ->back()
                ->with('success', 'Image updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update image: ' . $e->getMessage());
        }
    }

        /**
     * Delete a menu item.
     */
    public function merchantMenuDestroy(string $merchantId, string $menuItemId)
    {
        try {
            DB::beginTransaction();

            $menuItem = MenuItem::where('merchant_id', $merchantId)
                ->findOrFail($menuItemId);

            // Delete image
            if ($menuItem->image_url && Storage::disk('public')->exists($menuItem->image_url)) {
                Storage::disk('public')->delete($menuItem->image_url);
            }

            // Delete transactions
            $menuItem->transactions()->delete();

            // Delete menu item
            $menuItem->delete();

            DB::commit();

            return redirect()
                ->route('admin.merchants.menu', $merchantId)
                ->with('success', 'Menu item deleted successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete menu item: ' . $e->getMessage());
        }
    }


    /**
     * Add stock from admin merchant view.
     */
    public function merchantAddStock(Request $request, string $merchantId, string $menuItemId)
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
     * Remove stock from admin merchant view.
     */
    public function merchantRemoveStock(Request $request, string $merchantId, string $menuItemId)
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
     * Adjust stock from admin merchant view.
     */
    public function merchantAdjustStock(Request $request, string $merchantId, string $menuItemId)
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

    //     /**
    //  * Update the specified menu item.
    //  */
    // public function update(MenuItemRequest $request, string $id)
    // {
    //     try {
    //         DB::beginTransaction();

    //         $menuItem = MenuItem::findOrFail($id);
    //         $data = $request->validated();

    //         // Handle image upload
    //         if ($request->hasFile('image')) {
    //             // Delete old image
    //             if ($menuItem->image_url) {
    //                 Storage::disk('public')->delete($menuItem->image_url);
    //             }
    //             $imagePath = $request->file('image')->store('menu-items', 'public');
    //             $data['image_url'] = $imagePath;
    //         }

    //         $menuItem->update($data);

    //         DB::commit();

    //         return redirect()
    //             ->route('admin.menu-items.index')
    //             ->with('success', 'Menu item updated successfully!');

    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return back()
    //             ->withInput()
    //             ->with('error', 'Failed to update menu item: ' . $e->getMessage());
    //     }
    // }

    /**
     * Update only the image of a menu item.
     */
    public function updateImage(Request $request, string $id)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        try {
            DB::beginTransaction();

            $menuItem = MenuItem::findOrFail($id);
            
            // Delete old image
            if ($menuItem->image_url) {
                Storage::disk('public')->delete($menuItem->image_url);
            }
            
            // Store new image
            $imagePath = $request->file('image')->store('menu-items', 'public');
            $menuItem->image_url = $imagePath;
            $menuItem->save();

            DB::commit();

            return redirect()
                ->back()
                ->with('success', 'Image updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update image: ' . $e->getMessage());
        }
    }

    /**
     * Remove the image of a menu item.
     */
    public function removeImage(string $id)
    {
        try {
            DB::beginTransaction();

            $menuItem = MenuItem::findOrFail($id);
            
            if ($menuItem->image_url) {
                Storage::disk('public')->delete($menuItem->image_url);
                $menuItem->image_url = null;
                $menuItem->save();
            }

            DB::commit();

            return redirect()
                ->back()
                ->with('success', 'Image removed successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to remove image: ' . $e->getMessage());
        }
    }
        /**
     * Merchant update method (for merchant panel).
     */
    public function merchantUpdate(MenuItemRequest $request, string $id)
    {
        try {
            DB::beginTransaction();

            $merchant = auth()->user()->merchant;
            
            $menuItem = MenuItem::where('merchant_id', $merchant->merchant_id)
                ->findOrFail($id);
                
            $data = $request->validated();

            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image
                if ($menuItem->image_url) {
                    Storage::disk('public')->delete($menuItem->image_url);
                }
                $imagePath = $request->file('image')->store('menu-items', 'public');
                $data['image_url'] = $imagePath;
            }

            $menuItem->update($data);

            DB::commit();

            return redirect()
                ->route('merchant.menu-items.index')
                ->with('success', 'Menu item updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Failed to update menu item: ' . $e->getMessage());
        }
    }

    /**
     * Merchant update image only.
     */
    public function merchantUpdateImage(Request $request, string $id)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        try {
            DB::beginTransaction();

            $merchant = auth()->user()->merchant;
            
            $menuItem = MenuItem::where('merchant_id', $merchant->merchant_id)
                ->findOrFail($id);
            
            // Delete old image
            if ($menuItem->image_url) {
                Storage::disk('public')->delete($menuItem->image_url);
            }
            
            // Store new image
            $imagePath = $request->file('image')->store('menu-items', 'public');
            $menuItem->image_url = $imagePath;
            $menuItem->save();

            DB::commit();

            return redirect()
                ->back()
                ->with('success', 'Image updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update image: ' . $e->getMessage());
        }
    }

    /**
     * Merchant remove image.
     */
    public function merchantRemoveImage(string $id)
    {
        try {
            DB::beginTransaction();

            $merchant = auth()->user()->merchant;
            
            $menuItem = MenuItem::where('merchant_id', $merchant->merchant_id)
                ->findOrFail($id);
            
            if ($menuItem->image_url) {
                Storage::disk('public')->delete($menuItem->image_url);
                $menuItem->image_url = null;
                $menuItem->save();
            }

            DB::commit();

            return redirect()
                ->back()
                ->with('success', 'Image removed successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to remove image: ' . $e->getMessage());
        }
    }

}