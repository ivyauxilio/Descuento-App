<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Merchant\MenuItemRequest;
use App\Models\MenuItem;
use App\Models\Merchant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MenuItemController extends Controller
{
    /**
     * Get the authenticated merchant's menu items.
     */
    public function index(Request $request)
    {
        $merchant = $this->getMerchant();

        $query = MenuItem::where('merchant_id', $merchant->merchant_id);

        // Search filter
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Category filter
        // if ($request->filled('category')) {
        //     $query->where('category', $request->category);
        // }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Stock status filter
        if ($request->filled('stock_status')) {
            $query->where('stock_status', $request->stock_status);
        }

        // Featured filter
        if ($request->has('is_featured')) {
            $query->where('is_featured', filter_var($request->is_featured, FILTER_VALIDATE_BOOLEAN));
        }

        // Sort
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $menuItems = $query->paginate($request->get('per_page', 15));

        // Add statistics
        $stats = [
            'total' => MenuItem::where('merchant_id', $merchant->merchant_id)->count(),
            'available' => MenuItem::where('merchant_id', $merchant->merchant_id)
                ->where('status', 'available')
                ->count(),
            'unavailable' => MenuItem::where('merchant_id', $merchant->merchant_id)
                ->where('status', 'unavailable')
                ->count(),
            'out_of_stock' => MenuItem::where('merchant_id', $merchant->merchant_id)
                ->where('status', 'out_of_stock')
                ->count(),
            'in_stock' => MenuItem::where('merchant_id', $merchant->merchant_id)
                ->where('stock_status', 'in_stock')
                ->count(),
            'low_stock' => MenuItem::where('merchant_id', $merchant->merchant_id)
                ->where('stock_status', 'low_stock')
                ->count(),
            // 'featured' => MenuItem::where('merchant_id', $merchant->merchant_id)
            //     ->where('is_featured', true)
            //     ->count(),
            'total_value' => MenuItem::where('merchant_id', $merchant->merchant_id)
                ->sum(DB::raw('price * stock_quantity')),
        ];

        return response()->json([
            'data' => $menuItems,
            'stats' => $stats,
            'message' => 'Menu items retrieved successfully',
        ]);
    }

    /**
     * Store a new menu item.
     */
    public function store(MenuItemRequest $request)
    {
        try {
            DB::beginTransaction();

            $merchant = $this->getMerchant();

            $data = $request->validated();
            $data['merchant_id'] = $merchant->merchant_id;
            $data['stock_quantity'] = $request->stock_quantity ?? 0;
            $data['low_stock_threshold'] = $request->low_stock_threshold ?? 5;
            $data['unit'] = $request->unit ?? 'piece';
            // $data['is_featured'] = $request->is_featured ?? false;

            // Handle image upload
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('menu-items', 'public');
                $data['image_url'] = $imagePath;
            }

            // Handle ingredients (JSON)
            if ($request->has('ingredients')) {
                $data['ingredients'] = is_array($request->ingredients) 
                    ? $request->ingredients 
                    : explode(',', $request->ingredients);
            }

            // Handle variants (JSON)
            if ($request->has('variants')) {
                $data['variants'] = is_array($request->variants) 
                    ? $request->variants 
                    : json_decode($request->variants, true);
            }

            $menuItem = MenuItem::create($data);

            // Log initial stock if any
            if ($data['stock_quantity'] > 0) {
                $menuItem->addStock(
                    $data['stock_quantity'],
                    'Initial stock',
                    'initial',
                    null
                );
            }

            DB::commit();

            return response()->json([
                'data' => $menuItem->load('merchant'),
                'message' => 'Menu item created successfully',
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create menu item',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a single menu item.
     */
    public function show(string $id)
    {
        $merchant = $this->getMerchant();

        $menuItem = MenuItem::where('merchant_id', $merchant->merchant_id)
            ->with(['transactions'])
            ->findOrFail($id);

        return response()->json([
            'data' => $menuItem,
            'message' => 'Menu item retrieved successfully',
        ]);
    }

    /**
     * Update a menu item.
     */
    public function update(MenuItemRequest $request, string $id)
    {
        try {
            DB::beginTransaction();

            $merchant = $this->getMerchant();

            $menuItem = MenuItem::where('merchant_id', $merchant->merchant_id)
                ->findOrFail($id);

            $data = $request->validated();

            // Handle image upload
            if ($request->hasFile('image')) {
                if ($menuItem->image_url && Storage::disk('public')->exists($menuItem->image_url)) {
                    Storage::disk('public')->delete($menuItem->image_url);
                }
                $imagePath = $request->file('image')->store('menu-items', 'public');
                $data['image_url'] = $imagePath;
            }

            // Handle ingredients (JSON)
            if ($request->has('ingredients')) {
                $data['ingredients'] = is_array($request->ingredients) 
                    ? $request->ingredients 
                    : explode(',', $request->ingredients);
            }

            // Handle variants (JSON)
            if ($request->has('variants')) {
                $data['variants'] = is_array($request->variants) 
                    ? $request->variants 
                    : json_decode($request->variants, true);
            }

            $menuItem->update($data);

            DB::commit();

            return response()->json([
                'data' => $menuItem->load('merchant'),
                'message' => 'Menu item updated successfully',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update menu item',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a menu item.
     */
    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();

            $merchant = $this->getMerchant();

            $menuItem = MenuItem::where('merchant_id', $merchant->merchant_id)
                ->findOrFail($id);

            // Delete image
            if ($menuItem->image_url && Storage::disk('public')->exists($menuItem->image_url)) {
                Storage::disk('public')->delete($menuItem->image_url);
            }

            // Delete transactions
            $menuItem->transactions()->delete();

            // Delete menu item
            $menuItem->delete();

            DB::commit();

            return response()->json([
                'message' => 'Menu item deleted successfully',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to delete menu item',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Add stock to a menu item.
     */
    public function addStock(Request $request, string $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
            'reason' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $merchant = $this->getMerchant();

            $menuItem = MenuItem::where('merchant_id', $merchant->merchant_id)
                ->findOrFail($id);

            $menuItem->addStock(
                $request->quantity,
                $request->reason ?? 'Stock addition',
                'manual',
                null
            );

            DB::commit();

            return response()->json([
                'data' => $menuItem,
                'message' => "Added {$request->quantity} {$menuItem->unit}(s) to stock",
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to add stock',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove stock from a menu item.
     */
    public function removeStock(Request $request, string $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
            'reason' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $merchant = $this->getMerchant();

            $menuItem = MenuItem::where('merchant_id', $merchant->merchant_id)
                ->findOrFail($id);

            if ($menuItem->stock_quantity < $request->quantity) {
                return response()->json([
                    'message' => 'Not enough stock available',
                ], 422);
            }

            $menuItem->removeStock(
                $request->quantity,
                $request->reason ?? 'Stock removal',
                'manual',
                null
            );

            DB::commit();

            return response()->json([
                'data' => $menuItem,
                'message' => "Removed {$request->quantity} {$menuItem->unit}(s) from stock",
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to remove stock',
                'error' => $e->getMessage(),
            ], 500);
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
            DB::beginTransaction();

            $merchant = $this->getMerchant();

            $menuItem = MenuItem::where('merchant_id', $merchant->merchant_id)
                ->findOrFail($id);

            $menuItem->status = $request->status;
            $menuItem->save();

            DB::commit();

            return response()->json([
                'data' => $menuItem,
                'message' => 'Menu item status updated successfully',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get low stock items.
     */
    public function lowStock()
    {
        $merchant = $this->getMerchant();

        $lowStockItems = MenuItem::where('merchant_id', $merchant->merchant_id)
            ->where('stock_quantity', '<=', DB::raw('low_stock_threshold'))
            ->where('stock_quantity', '>', 0)
            ->orderBy('stock_quantity')
            ->get();

        $outOfStockItems = MenuItem::where('merchant_id', $merchant->merchant_id)
            ->where('stock_quantity', 0)
            ->orderBy('name')
            ->get();

        return response()->json([
            'low_stock' => $lowStockItems,
            'out_of_stock' => $outOfStockItems,
            'message' => 'Low stock items retrieved successfully',
        ]);
    }

    /**
     * Get menu item categories.
     */
    public function categories()
    {
        $merchant = $this->getMerchant();

        $categories = MenuItem::where('merchant_id', $merchant->merchant_id)
            ->whereNotNull('category')
            ->distinct('category')
            ->pluck('category');

        return response()->json([
            'data' => $categories,
            'message' => 'Categories retrieved successfully',
        ]);
    }

    /**
     * Get the authenticated merchant.
     */
    private function getMerchant()
    {
        $merchant = auth()->user()->merchant;

        if (!$merchant) {
            abort(404, 'Merchant not found');
        }

        return $merchant;
    }
}