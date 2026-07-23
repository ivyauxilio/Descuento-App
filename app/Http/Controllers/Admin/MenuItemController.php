<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MenuItemRequest;
use App\Models\MenuItem;
use App\Models\Merchant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
        $statuses = ['available', 'unavailable', 'out_of_stock'];

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
            
            // Handle image upload
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('menu-items', 'public');
                $data['image_url'] = $imagePath;
            }

            $menuItem = MenuItem::create($data);

            DB::commit();

            return redirect()
                ->route('admin.menu-items.index')
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

            $menuItem = MenuItem::findOrFail($id);
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
                ->route('admin.menu-items.index')
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
                ->route('admin.menu-items.index')
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
}