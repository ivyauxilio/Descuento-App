<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PromotionRequest;
use App\Models\Promotion;
use App\Models\Merchant;
use App\Models\Category;        
use App\Models\MenuItem; 
use Illuminate\Http\Request;

class AdminPromotionController extends Controller
{
    /**
     * Display a listing of all promotions.
     */
    public function index(Request $request)
    {
        $query = Promotion::with(['merchant', 'freeMenuItem', 'requiredMenuItem', 'category']);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('promo_type', 'like', "%{$search}%")
                  ->orWhereHas('merchant', function ($q2) use ($search) {
                      $q2->where('business_name', 'like', "%{$search}%");
                  });
            });
        }

        // Merchant filter
        if ($request->filled('merchant_id')) {
            $query->where('merchant_id', $request->merchant_id);
        }

        // Promo type filter
        if ($request->filled('promo_type')) {
            $query->where('promo_type', $request->promo_type);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Date filter
        if ($request->filled('date_range')) {
            switch ($request->date_range) {
                case 'today':
                    $query->whereDate('start_date', '<=', now())
                          ->where(function ($q) {
                              $q->whereNull('end_date')
                                ->orWhereDate('end_date', '>=', now());
                          });
                    break;
                case 'active':
                    $query->where('status', 'active')
                          ->whereDate('start_date', '<=', now())
                          ->where(function ($q) {
                              $q->whereNull('end_date')
                                ->orWhereDate('end_date', '>=', now());
                          });
                    break;
                case 'expired':
                    $query->where(function ($q) {
                        $q->where('status', 'expired')
                          ->orWhereDate('end_date', '<', now());
                    });
                    break;
                case 'upcoming':
                    $query->whereDate('start_date', '>', now());
                    break;
            }
        }

        // Sort
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $promotions = $query->paginate(15);
        $merchants = Merchant::orderBy('business_name')->get();
        $promoTypes = ['percentage', 'fixed', 'bogo'];
        $statuses = ['active', 'inactive', 'expired'];

        // Get statistics
        $stats = [
            'total' => Promotion::count(),
            'active' => Promotion::where('status', 'active')
                ->whereDate('start_date', '<=', now())
                ->where(function ($q) {
                    $q->whereNull('end_date')
                      ->orWhereDate('end_date', '>=', now());
                })
                ->count(),
            'expired' => Promotion::where(function ($q) {
                $q->where('status', 'expired')
                  ->orWhereDate('end_date', '<', now());
            })->count(),
            'inactive' => Promotion::where('status', 'inactive')->count(),
            'by_type' => [
                'percentage' => Promotion::where('promo_type', 'percentage')->count(),
                'fixed' => Promotion::where('promo_type', 'fixed')->count(),
                'bogo' => Promotion::where('promo_type', 'bogo')->count(),
            ],
            'by_merchant' => Promotion::select('merchant_id')
                ->with('merchant')
                ->selectRaw('count(*) as total')
                ->groupBy('merchant_id')
                ->orderBy('total', 'desc')
                ->limit(5)
                ->get(),
        ];

        return view('admin.promotions.index', compact(
            'promotions',
            'merchants',
            'promoTypes',
            'statuses',
            'stats'
        ));
    }

    public function create()
    {
        $merchants = Merchant::orderBy('business_name')->get();
        $categories = Category::orderBy('name')->get();
        $menuItems = MenuItem::with('merchant')->orderBy('name')->get();
        $promoTypes = ['percentage', 'fixed', 'bogo'];
        $statuses = ['active', 'inactive', 'expired'];

        return view('admin.promotions.create', compact(
            'merchants', 'categories', 'menuItems', 'promoTypes', 'statuses'
        ));
    }
    
    public function store(PromotionRequest $request)
    {
        try {
            $promotion = Promotion::create($request->validated());

            return redirect()
                ->route('admin.promotions.index')
                ->with('success', 'Promotion created successfully!');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to create promotion: ' . $e->getMessage());
        }
    }
    /**
     * Display the specified promotion.
     */
    public function show(string $id)
    {
        $promotion = Promotion::with([
            'merchant',
            'freeMenuItem',
            'requiredMenuItem',
            'category',
            'redemptions'
        ])->findOrFail($id);

        return view('admin.promotions.show', compact('promotion'));
    }

        public function edit(string $id)
    {
        $promotion = Promotion::findOrFail($id);
        $merchants = Merchant::orderBy('business_name')->get();
        $categories = Category::orderBy('name')->get();
        $menuItems = MenuItem::with('merchant')->orderBy('name')->get();
        $promoTypes = ['percentage', 'fixed', 'bogo'];
        $statuses = ['active', 'inactive', 'expired'];

        return view('admin.promotions.edit', compact(
            'promotion', 'merchants', 'categories', 'menuItems', 'promoTypes', 'statuses'
        ));
    }

    /**
     * Update the specified promotion.
     */
    public function update(PromotionRequest $request, string $id)
    {
        try {
            $promotion = Promotion::findOrFail($id);
            $promotion->update($request->validated());

            return redirect()
                ->route('admin.promotions.index')
                ->with('success', 'Promotion updated successfully!');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to update promotion: ' . $e->getMessage());
        }
    }

    /**
     * Update promotion status.
     */
    public function updateStatus(Request $request, string $id)
    {
        $request->validate([
            'status' => ['required', 'in:active,inactive,expired'],
        ]);

        try {
            $promotion = Promotion::findOrFail($id);
            $promotion->status = $request->status;
            $promotion->save();

            return redirect()
                ->route('admin.promotions.index')
                ->with('success', 'Promotion status updated successfully!');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update status: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified promotion.
     */
    public function destroy(string $id)
    {
        try {
            $promotion = Promotion::findOrFail($id);
            $promotion->delete();

            return redirect()
                ->route('admin.promotions.index')
                ->with('success', 'Promotion deleted successfully!');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete promotion: ' . $e->getMessage());
        }
    }

    /**
     * Bulk delete promotions.
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:promotions,promotion_id'
        ]);

        try {
            Promotion::whereIn('promotion_id', $request->ids)->delete();

            return redirect()
                ->route('admin.promotions.index')
                ->with('success', count($request->ids) . ' promotions deleted successfully!');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete promotions: ' . $e->getMessage());
        }
    }

    /**
     * Get promotion statistics for dashboard.
     */
    public function getStats()
    {
        $stats = [
            'total' => Promotion::count(),
            'active' => Promotion::where('status', 'active')
                ->whereDate('start_date', '<=', now())
                ->where(function ($q) {
                    $q->whereNull('end_date')
                      ->orWhereDate('end_date', '>=', now());
                })
                ->count(),
            'expired' => Promotion::where(function ($q) {
                $q->where('status', 'expired')
                  ->orWhereDate('end_date', '<', now());
            })->count(),
            'inactive' => Promotion::where('status', 'inactive')->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Export promotions (CSV).
     */
    public function export(Request $request)
    {
        $promotions = Promotion::with(['merchant', 'freeMenuItem'])
            ->when($request->status, function ($q) use ($request) {
                return $q->where('status', $request->status);
            })
            ->get();

        $filename = 'promotions_' . now()->format('Y-m-d') . '.csv';
        $handle = fopen('php://output', 'w');

        // Add headers
        fputcsv($handle, [
            'Title',
            'Type',
            'Value',
            'Merchant',
            'Min Order',
            'Start Date',
            'End Date',
            'Status'
        ]);

        // Add data
        foreach ($promotions as $promotion) {
            fputcsv($handle, [
                $promotion->title,
                $promotion->promo_type,
                $promotion->value,
                $promotion->merchant->business_name ?? 'N/A',
                $promotion->min_order_amount ?? 'N/A',
                $promotion->start_date->format('Y-m-d'),
                $promotion->end_date ? $promotion->end_date->format('Y-m-d') : 'No Expiry',
                $promotion->status,
            ]);
        }

        fclose($handle);

        return response()
            ->stream(function () use ($handle) {
                // Output already handled
            })
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}