<?php

namespace App\Services;

use App\Models\User;
use App\Models\PhysicalCard;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PurchaseService
{
    protected ReferralService $referrals;

    public function __construct(ReferralService $referrals)
    {
        $this->referrals = $referrals;
    }

    /**
     * Called whenever ANY purchase is successfully paid.
     *
     * This handles:
     * - Referral qualification (if the buyer was referred)
     * - Future: loyalty points, cashback, etc.
     *
     * @param  User    $buyer
     * @param  string  $reference       Order number / card serial / payment ref
     * @param  float   $amount
     * @param  array   $meta            Extra context (order_id, card_id, etc.)
     */
    public function handleSuccessfulPurchase(
        User $buyer,
        string $reference,
        float $amount,
        array $meta = []
    ): void {
        try {
            DB::transaction(function () use ($buyer, $reference, $amount, $meta) {
                // ✅ 1. Qualify any pending referral for this buyer
                $referral = $this->referrals->qualifyReferral(
                    $buyer,
                    $reference,
                    $amount
                );

                if ($referral) {
                    Log::info('Referral qualified from purchase', [
                        'buyer_id' => $buyer->id,
                        'referral_id' => $referral->referral_id,
                        'reference' => $reference,
                        'amount' => $amount,
                    ]);
                }

                // ✅ 2. Future hooks go here (loyalty points, cashback, etc.)
                // $this->loyalty->award($buyer, $amount);
            });
        } catch (\Exception $e) {
            Log::error('handleSuccessfulPurchase failed', [
                'buyer_id' => $buyer->id,
                'reference' => $reference,
                'error' => $e->getMessage(),
            ]);
            // Don't throw — the purchase itself already succeeded
        }
    }

    /**
     * Convenience: handle a physical card purchase.
     */
    public function handleCardPurchase(PhysicalCard $card, float $amount): void
    {
        if (!$card->user) {
            return;
        }

        $this->handleSuccessfulPurchase(
            $card->user,
            'CARD-' . $card->card_number,
            $amount,
            ['card_id' => $card->card_id]
        );
    }

    /**
     * Convenience: handle an order purchase.
     */
    public function handleOrderPurchase(Order $order): void
    {
        if (!$order->customer) {
            return;
        }

        $this->handleSuccessfulPurchase(
            $order->customer,
            $order->order_number,
            (float) $order->total_amount,
            [
                'order_id' => $order->order_id,
                'promotion_id' => $order->promotion_id,
            ]
        );
    }
}