<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use App\Models\Merchant;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    /**
     * Display promotions for the merchant.
     */
    public function index(Request $request)
    {
        // Get the authenticated merchant
        $merchant = auth()->user()->merchant;
        
        if (!$merchant) {
            return redirect()->route('merchant.dashboard')
                ->with('error', 'You are not associated with any merchant.');
        }

        $query = Promotion::where('merchant_id', $merchant->merchant_id);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by promo type
        if ($request->filled('promo_type')) {
            $query->where('promo_type', $request->promo_type);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('promo_type', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $promotions = $query->paginate(12);
        
        // Get stats
        $stats = [
            'total' => Promotion::where('merchant_id', $merchant->merchant_id)->count(),
            'active' => Promotion::where('merchant_id', $merchant->merchant_id)
                ->where('status', 'active')
                ->where('start_date', '<=', now())
                ->where(function ($q) {
                    $q->whereNull('end_date')
                      ->orWhere('end_date', '>=', now());
                })
                ->count(),
            'expired' => Promotion::where('merchant_id', $merchant->merchant_id)
                ->where(function ($q) {
                    $q->where('status', 'expired')
                      ->orWhere('end_date', '<', now());
                })
                ->count(),
            'inactive' => Promotion::where('merchant_id', $merchant->merchant_id)
                ->where('status', 'inactive')
                ->count(),
        ];

        $promoTypes = ['percentage', 'fixed', 'bogo'];
        $statuses = ['active', 'inactive', 'expired'];

        return view('merchant.promotions.index', compact(
            'promotions', 
            'stats', 
            'promoTypes', 
            'statuses',
            'merchant'
        ));
    }

    /**
     * Display promotion details.
     */
    public function show(string $id)
    {
        $merchant = auth()->user()->merchant;
        
        if (!$merchant) {
            return redirect()->route('merchant.dashboard')
                ->with('error', 'You are not associated with any merchant.');
        }

        $promotion = Promotion::where('merchant_id', $merchant->merchant_id)
            ->with(['freeMenuItem', 'requiredMenuItem', 'category'])
            ->findOrFail($id);

        return view('merchant.promotions.show', compact('promotion', 'merchant'));
    }

    /**
     * Get promotion statistics for dashboard.
     */
    public function getStats()
    {
        $merchant = auth()->user()->merchant;
        
        if (!$merchant) {
            return response()->json(['error' => 'No merchant found'], 404);
        }

        $stats = [
            'total' => Promotion::where('merchant_id', $merchant->merchant_id)->count(),
            'active' => Promotion::where('merchant_id', $merchant->merchant_id)
                ->where('status', 'active')
                ->where('start_date', '<=', now())
                ->where(function ($q) {
                    $q->whereNull('end_date')
                      ->orWhere('end_date', '>=', now());
                })
                ->count(),
            'expired' => Promotion::where('merchant_id', $merchant->merchant_id)
                ->where(function ($q) {
                    $q->where('status', 'expired')
                      ->orWhere('end_date', '<', now());
                })
                ->count(),
        ];

        return response()->json($stats);
    }
}