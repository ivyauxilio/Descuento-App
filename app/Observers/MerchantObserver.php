<?php

namespace App\Observers;

use App\Models\Merchant;
use App\Models\MerchantWallet;
use App\Models\CreditTransaction;

class MerchantObserver
{
    /**
     * When a new merchant is created, auto-create wallet and grant welcome bonus
     */
    public function created(Merchant $merchant)
    {
        // Create wallet
        $wallet = MerchantWallet::create([
            'merchant_id' => $merchant->merchant_id,
            'credit_balance' => 0,
            'total_credits_purchased' => 0,
            'total_credits_used' => 0,
            'total_spent' => 0,
        ]);

        // Grant welcome bonus
        $wallet->claimWelcomeBonus(10);
    }
}