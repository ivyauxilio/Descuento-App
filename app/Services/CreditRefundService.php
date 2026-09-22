<?php
// app/Services/CreditRefundService.php

namespace App\Services;

use App\Models\Promotion;
use App\Models\MerchantWallet;
use App\Models\CreditTransaction;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreditRefundService
{
    /**
     * Refund credits for a cancelled promotion
     */
    public function refundPromotion(Promotion $promotion, string $reason = 'Promotion cancelled'): array
    {
        // Check if already refunded
        $existingRefund = CreditTransaction::where('promotion_id', $promotion->promotion_id)
            ->where('type', 'refund')
            ->exists();

        if ($existingRefund) {
            return [
                'success' => false,
                'message' => 'Credits already refunded for this promotion.',
            ];
        }

        $creditsUsed = $promotion->credits_used ?? 0;
        
        if ($creditsUsed <= 0) {
            return [
                'success' => false,
                'message' => 'No credits were used for this promotion.',
            ];
        }

        // Get refund percentage (default 100%)
        $refundPercentage = SystemSetting::get('credits.refund_percentage', 100);
        $refundAmount = (int) floor($creditsUsed * ($refundPercentage / 100));

        if ($refundAmount <= 0) {
            return [
                'success' => false,
                'message' => "Refund amount is 0 (refund policy: {$refundPercentage}%).",
            ];
        }

        try {
            DB::beginTransaction();

            $wallet = MerchantWallet::where('merchant_id', $promotion->merchant_id)
                ->lockForUpdate()
                ->firstOrFail();

            $before = $wallet->credit_balance;
            $wallet->credit_balance += $refundAmount;
            $wallet->total_credits_used = max(0, $wallet->total_credits_used - $refundAmount);
            $wallet->save();

            // Create refund transaction
            CreditTransaction::create([
                'merchant_id' => $promotion->merchant_id,
                'type' => 'refund',
                'credits' => $refundAmount,
                'balance_before' => $before,
                'balance_after' => $wallet->credit_balance,
                'amount' => 0,
                'promotion_id' => $promotion->promotion_id,
                'description' => "Refund for cancelled promotion: {$promotion->title}",
                'metadata' => [
                    'reason' => $reason,
                    'original_credits_used' => $creditsUsed,
                    'refund_percentage' => $refundPercentage,
                    'refunded_at' => now()->toIso8601String(),
                ],
            ]);

            // Mark promotion as refunded
            $promotion->credits_refunded = true;
            $promotion->credits_refunded_at = now();
            $promotion->save();

            DB::commit();

            Log::info('Credits refunded', [
                'merchant_id' => $promotion->merchant_id,
                'promotion_id' => $promotion->promotion_id,
                'amount' => $refundAmount,
            ]);

            return [
                'success' => true,
                'credits_refunded' => $refundAmount,
                'new_balance' => $wallet->credit_balance,
                'message' => "Refunded {$refundAmount} credits ({$refundPercentage}%).",
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Refund failed: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Refund failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Manually adjust credits (admin only)
     */
    public function adjustCredits(
        string $merchantId, 
        int $amount, 
        string $reason,
        ?string $adminUserId = null
    ): array {
        try {
            DB::beginTransaction();

            $wallet = MerchantWallet::where('merchant_id', $merchantId)
                ->lockForUpdate()
                ->firstOrFail();

            $before = $wallet->credit_balance;
            $wallet->credit_balance += $amount;

            if ($amount > 0) {
                $wallet->total_credits_purchased += $amount;
            } else {
                $wallet->total_credits_used += abs($amount);
            }

            $wallet->save();

            CreditTransaction::create([
                'merchant_id' => $merchantId,
                'type' => $amount > 0 ? 'bonus' : 'adjustment',
                'credits' => $amount,
                'balance_before' => $before,
                'balance_after' => $wallet->credit_balance,
                'description' => $reason,
                'metadata' => [
                    'admin_user_id' => $adminUserId ?? auth()->id(),
                    'adjusted_at' => now()->toIso8601String(),
                ],
            ]);

            DB::commit();

            return [
                'success' => true,
                'new_balance' => $wallet->credit_balance,
                'message' => "Adjusted {$amount} credits.",
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Adjustment failed: ' . $e->getMessage(),
            ];
        }
    }
}