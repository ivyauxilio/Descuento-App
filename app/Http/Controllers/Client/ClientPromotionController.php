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
}