<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Merchant\PromotionRequest;
use App\Models\Promotion;
use App\Models\Merchant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PromotionController extends Controller
{
    /**
     * Display promotions for the merchant.
     */
    public function index(Request $request)
    {
        // Get the authenticated merchant
        $merchant = $this->getMerchant();
        
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

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('promo_type', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        // $promotions = $query->paginate(12);
        $promotions = $query->paginate($request->get('per_page', 15));
        
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

        // $promoTypes = ['percentage', 'fixed', 'bogo'];
        // $statuses = ['active', 'inactive', 'expired'];

        // return view('merchant.promotions.index', compact(
        //     'promotions', 
        //     'stats', 
        //     'promoTypes', 
        //     'statuses',
        //     'merchant'
        // ));
        return response()->json([
            'data' => $promotions,
            'stats' => $stats,
            'message' => 'Promotions retrieved successfully',
        ]);
    }


        /**
     * Store a newly created promotion.
     */
    public function store(PromotionRequest $request)
    {
        try {
            DB::beginTransaction();

            $merchant = $this->getMerchant();

            $data = $request->validated();
            $data['merchant_id'] = $merchant->merchant_id;

            // Set default values
            $data['promotion_id'] = (string) Str::uuid();
            $data['status'] = $request->status ?? 'active';

            // Set value to 0 for BOGO if not provided
            if ($data['promo_type'] === 'bogo' && !isset($data['value'])) {
                $data['value'] = 0;
            }

            $promotion = Promotion::create($data);

            DB::commit();

            return response()->json([
                'data' => $promotion->load('merchant'),
                'message' => 'Promotion created successfully',
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create promotion',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Display promotion details.
     */
    public function show(string $id)
    {
        $merchant = $this->getMerchant();
        
        if (!$merchant) {
            return redirect()->route('merchant.dashboard')
                ->with('error', 'You are not associated with any merchant.');
        }

        $promotion = Promotion::where('merchant_id', $merchant->merchant_id)
            ->findOrFail($id);

        return response()->json([
            'data' => $promotion,
            'message' => 'Promotion retrieved successfully',
        ]);
        // return view('merchant.promotions.show', compact('promotion', 'merchant'));
    }

    /**
     * Update the specified promotion.
     */
    public function update(PromotionRequest $request, string $id)
    {
        try {
            DB::beginTransaction();

            $merchant = $this->getMerchant();

            $promotion = Promotion::where('merchant_id', $merchant->merchant_id)
                ->findOrFail($id);

            $data = $request->validated();

            // Set value to 0 for BOGO if not provided
            if ($data['promo_type'] === 'bogo' && !isset($data['value'])) {
                $data['value'] = 0;
            }

            $promotion->update($data);

            DB::commit();

            return response()->json([
                'data' => $promotion->load('merchant'),
                'message' => 'Promotion updated successfully',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update promotion',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

        /**
     * Remove the specified promotion.
     */
    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();

            $merchant = $this->getMerchant();

            $promotion = Promotion::where('merchant_id', $merchant->merchant_id)
                ->findOrFail($id);

            $promotion->delete();

            DB::commit();

            return response()->json([
                'message' => 'Promotion deleted successfully',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to delete promotion',
                'error' => $e->getMessage(),
            ], 500);
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
            DB::beginTransaction();

            $merchant = $this->getMerchant();

            $promotion = Promotion::where('merchant_id', $merchant->merchant_id)
                ->findOrFail($id);

            $promotion->status = $request->status;
            $promotion->save();

            DB::commit();

            return response()->json([
                'data' => $promotion,
                'message' => 'Promotion status updated successfully',
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

    private function getMerchant()
    {
        $merchant = auth()->user()->merchant;

        if (!$merchant) {
            abort(404, 'Merchant not found');
        }

        return $merchant;
    }

}