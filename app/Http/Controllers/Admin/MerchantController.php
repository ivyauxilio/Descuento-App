<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MerchantRequest;
use App\Models\Category;
use App\Models\Merchant;
use App\Models\Province;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MerchantController extends Controller
{
    /**
     * Display a listing of merchants.
     */
    public function index(Request $request)
    {
        $query = Merchant::with(['owner', 'category', 'province', 'approver']);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('business_name', 'like', "%{$search}%")
                  ->orWhere('branch_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Category filter
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // Sort
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $merchants = $query->paginate(15);
        $statuses = ['pending', 'approved', 'active', 'rejected', 'suspended'];
        $categories = Category::orderBy('name')->get();

        return view('admin.merchants.index', compact('merchants', 'statuses', 'categories'));
    }

    /**
     * Show the form for creating a new merchant.
     */
    public function create()
    {
        $users = User::where('role', 'merchant')
            ->orWhere('role', 'admin')
            ->orderBy('firstname')
            ->get()
            ->map(function ($user) {
                $user->full_name = $user->firstname . ' ' . $user->lastname . ' (' . $user->email . ')';
                return $user;
            });

        $categories = Category::orderBy('name')->get();
        $provinces = Province::orderBy('name')->get();
        $statuses = ['pending', 'approved', 'active', 'rejected', 'suspended'];

        return view('admin.merchants.create', compact('users', 'categories', 'provinces', 'statuses'));
    }

    /**
     * Store a newly created merchant.
     */
    public function store(MerchantRequest $request)
    {
        try {
            DB::beginTransaction();

            $merchant = Merchant::create([
                'owner_id' => $request->owner_id,
                'category_id' => $request->category_id,
                'province_id' => $request->province_id,
                'business_name' => $request->business_name,
                'branch_name' => $request->branch_name,
                'email' => $request->email,
                'street_address' => $request->street_address,
                'city' => $request->city,
                'status' => $request->status,
                'approved_by' => in_array($request->status, ['approved', 'active']) 
                    ? auth()->id() 
                    : null,
            ]);

            DB::commit();

            return redirect()
                ->route('admin.merchants.index')
                ->with('success', 'Merchant created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Failed to create merchant: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified merchant.
     */
    public function show(string $id)
    {
        $merchant = Merchant::with(['owner', 'category', 'province', 'approver'])
            ->findOrFail($id);

        return view('admin.merchants.show', compact('merchant'));
    }

    /**
     * Show the form for editing the specified merchant.
     */
    public function edit(string $id)
    {
        $merchant = Merchant::findOrFail($id);
        
        $users = User::where('role', 'merchant')
            ->orWhere('role', 'admin')
            ->orderBy('firstname')
            ->get()
            ->map(function ($user) {
                $user->full_name = $user->firstname . ' ' . $user->lastname . ' (' . $user->email . ')';
                return $user;
            });

        $categories = Category::orderBy('name')->get();
        $provinces = Province::orderBy('name')->get();
        $statuses = ['pending', 'approved', 'active', 'rejected', 'suspended'];

        return view('admin.merchants.edit', compact('merchant', 'users', 'categories', 'provinces', 'statuses'));
    }

    /**
     * Update the specified merchant.
     */
    public function update(MerchantRequest $request, string $id)
    {
        try {
            DB::beginTransaction();

            $merchant = Merchant::findOrFail($id);
            
            $data = $request->validated();
            
            // Set approved_by if status is approved or active
            if (in_array($request->status, ['approved', 'active']) && $merchant->status !== $request->status) {
                $data['approved_by'] = auth()->id();
            }

            $merchant->update($data);

            DB::commit();

            return redirect()
                ->route('admin.merchants.index')
                ->with('success', 'Merchant updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Failed to update merchant: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified merchant.
     */
    public function destroy(string $id)
    {
        try {
            $merchant = Merchant::findOrFail($id);
            $merchant->delete();

            return redirect()
                ->route('admin.merchants.index')
                ->with('success', 'Merchant deleted successfully!');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete merchant: ' . $e->getMessage());
        }
    }

    /**
     * Bulk delete merchants.
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:merchants,merchant_id'
        ]);

        try {
            Merchant::whereIn('merchant_id', $request->ids)->delete();

            return redirect()
                ->route('admin.merchants.index')
                ->with('success', count($request->ids) . ' merchants deleted successfully!');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete merchants: ' . $e->getMessage());
        }
    }

    /**
     * Update merchant status.
     */
    public function updateStatus(Request $request, string $id)
    {
        $request->validate([
            'status' => ['required', 'in:pending,approved,active,rejected,suspended'],
        ]);

        try {
            $merchant = Merchant::findOrFail($id);
            $merchant->status = $request->status;
            
            // Set approved_by if status is approved or active
            if (in_array($request->status, ['approved', 'active'])) {
                $merchant->approved_by = auth()->id();
            }
            
            $merchant->save();

            return redirect()
                ->route('admin.merchants.index')
                ->with('success', 'Merchant status updated successfully!');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update status: ' . $e->getMessage());
        }
    }
}