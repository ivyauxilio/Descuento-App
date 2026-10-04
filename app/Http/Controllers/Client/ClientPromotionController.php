<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use App\Models\Merchant;
use Illuminate\Http\Request;

class ClientPromotionController extends Controller
{
    public function index(Request $request)
    {
        $promotions = Promotion::with(['merchant'])
            ->where('status', 'active')
            ->whereDate('start_date', '<=', now())
            ->where(function ($q) {
                $q->whereNull('end_date')
                  ->orWhereDate('end_date', '>=', now());
            })
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($promotion) {
                return [
                    'promotion_id' => $promotion->promotion_id,
                    'title' => $promotion->title,
                    'description' => $promotion->description,
                    'promo_type' => $promotion->promo_type,
                    'value' => $promotion->value,
                    'merchant' => [
                        'business_name' => $promotion->merchant->business_name ?? null,
                        'logo_url' => $promotion->merchant->logo_url ?? null,
                        'category' => $promotion->merchant->category ?? null,
                    ],
                    'poster_image' => $promotion->poster_image_url,
                    'poster_thumbnail' => $promotion->poster_thumbnail_url,
                    'status' => $promotion->status,
                    'end_date' => $promotion->end_date,
                    'qr_code' => $promotion->qr_code,
                    'min_order_amount' => $promotion->min_order_amount,
                    'usage_limit' => $promotion->usage_limit,
                    'total_usage_limit' => $promotion->total_usage_limit,
                    'used_count' => $promotion->used_count,
                ];
            });

        return response()->json([
            'data' => $promotions,
            'message' => 'Promotions retrieved successfully',
        ]);
    }

    public function show(string $id)
    {
        $promotion = Promotion::with(['merchant'])
            ->where('status', 'active')
            ->where('promotion_id', $id)
            ->first();

        if (!$promotion) {
            return response()->json([
                'message' => 'Promotion not found',
            ], 404);
        }

        return response()->json([   
            'data' => [
                'promotion_id' => $promotion->promotion_id,
                'title' => $promotion->title,
                'description' => $promotion->description,
                'promo_type' => $promotion->promo_type,
                'value' => $promotion->value,
                'merchant' => [
                    'business_name' => $promotion->merchant->business_name ?? null,
                    'logo_url' => $promotion->merchant->logo_url ?? null,
                ],
                'poster_image' => $promotion->poster_image_url,
                'poster_thumbnail' => $promotion->poster_thumbnail_url,
                'status' => $promotion->status,
                'end_date' => $promotion->end_date,
                'qr_code' => $promotion->qr_code,
                'min_order_amount' => $promotion->min_order_amount,
                'usage_limit' => $promotion->usage_limit,
                'total_usage_limit' => $promotion->total_usage_limit,
                'used_count' => $promotion->used_count,
            ],
            'message' => 'Promotion retrieved successfully',
        ]);
    }

    public function validate(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'subtotal' => 'required|numeric|min:0',
        ]);

        $promotion = Promotion::where('code', $request->code)
            ->where('status', 'active')
            ->where('start_date', '<=', now())
            ->where(function ($q) {
                $q->where('end_date', '>=', now())
                ->orWhereNull('end_date');
            })
            ->first();

        if (!$promotion) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired promo code.',
            ], 422);
        }

        $subtotal = (float) $request->subtotal;

        // Min order check
        if ($promotion->min_order_amount && $subtotal < $promotion->min_order_amount) {
            return response()->json([
                'success' => false,
                'message' => 'Minimum order of ₱' . number_format($promotion->min_order_amount, 2) . ' required.',
            ], 422);
        }

        // Usage limit
        if ($promotion->usage_limit && $promotion->used_count >= $promotion->usage_limit) {
            return response()->json([
                'success' => false,
                'message' => 'This promo has reached its usage limit.',
            ], 422);
        }

        // Calculate discount
        $discount = 0;
        if ($promotion->promo_type === 'percentage') {
            $discount = $subtotal * ($promotion->value / 100);
            if ($promotion->max_discount_amount) {
                $discount = min($discount, (float) $promotion->max_discount_amount);
            }
        } elseif ($promotion->promo_type === 'fixed') {
            $discount = min((float) $promotion->value, $subtotal);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'promotion_id' => $promotion->promotion_id,
                'code' => $promotion->code,
                'title' => $promotion->title,
                'type' => $promotion->promo_type,
                'value' => $promotion->value,
                'discount' => round($discount, 2),
            ],
            'message' => 'Promo applied! You save ₱' . number_format($discount, 2),
        ]);
    }
}