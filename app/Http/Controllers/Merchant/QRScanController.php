<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\QrCodeUsage;

class QRScanController extends Controller
{
    /**
     * Redeem a promotion from QR code scan.
     */
    public function redeem(Request $request)
    {
        $request->validate([
            'promotion_id' => 'required|uuid',
            'user_id' => 'required|string',
            'redemption_token' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Get the merchant
            $merchant = auth()->user()->merchant;
            if (!$merchant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Merchant account not found.',
                ], 404);
            }

            // Find the promotion
            $promotion = Promotion::where('merchant_id', $merchant->merchant_id)
                ->where('promotion_id', $request->promotion_id)
                ->first();

            if (!$promotion) {
                return response()->json([
                    'success' => false,
                    'message' => 'Promotion not found.',
                ], 404);
            }

            // Check if promotion is active
            if (!$promotion->isValidQrCode()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This promotion is no longer valid',
                ], 422);
            }

            // Check usage limit
            if ($promotion->usage_limit && $promotion->used_count >= $promotion->usage_limit) {
                return response()->json([
                    'success' => false,
                    'message' => 'Promotion usage limit reached',
                ], 422);
            }

            // Check if user exists
            // $user = User::find($request->user_id);
            $user = User::where('uuid', $request->user_id)->first();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found.',
                ], 404);
            }

            // Validate token (anti-replay attack)
            if ($request->redemption_token) {
                // Check if token was already used
                $existingUsage = QrCodeUsage::where('promotion_id', $promotion->promotion_id)
                    ->where('qr_code', $promotion->qr_code)
                    ->where('user_id', $user->id)
                    ->exists();

                if ($existingUsage) {
                    return response()->json([
                        'success' => false,
                        'message' => 'This QR code has already been used.',
                    ], 422);
                }
            }

            // Redeem the promotion
            $result = $promotion->redeem($user->id, $merchant->merchant_id);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ], 422);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => array_merge($result['data'], [
                    'promotion_id' => $promotion->promotion_id,
                    'title' => $promotion->title,
                    'user_name' => $user->firstname . ' ' . $user->lastname,
                    'user_email' => $user->email,
                    'discount_text' => $this->getDiscountText($promotion),
                    'merchant_name' => $merchant->business_name,
                ]),
                'message' => $result['message'],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('QR redemption failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to redeem promotion: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get discount display text.
     */
    private function getDiscountText($promotion): string
    {
        if ($promotion->promo_type === 'percentage') {
            return $promotion->value . '% OFF';
        }
        if ($promotion->promo_type === 'fixed') {
            return '₱' . number_format($promotion->value, 2) . ' OFF';
        }
        if ($promotion->promo_type === 'bogo') {
            return 'Buy 1 Get 1 Free';
        }
        return 'Special Offer';
    }
}