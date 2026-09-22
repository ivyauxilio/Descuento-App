<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Models\Merchant;
use App\Models\MerchantWallet;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Merchant::chunk(100, function ($merchants) {
            foreach ($merchants as $merchant) {
                $wallet = MerchantWallet::firstOrCreate(
                    ['merchant_id' => $merchant->merchant_id],
                    ['credit_balance' => 0]
                );

                // Grant 10 free credits to existing merchants who haven't claimed
                if (!$wallet->welcome_bonus_claimed) {
                    $wallet->claimWelcomeBonus(10);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};