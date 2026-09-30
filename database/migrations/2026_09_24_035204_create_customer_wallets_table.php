<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('customer_wallets', function (Blueprint $table) {
            $table->id('wallet_id');
            $table->unsignedBigInteger('user_id')->unique();

            // Money
            $table->decimal('balance', 12, 2)->default(0);              // Available to withdraw
            $table->decimal('pending_balance', 12, 2)->default(0);      // Awaiting admin approval
            $table->decimal('total_earned', 12, 2)->default(0);         // Lifetime earnings
            $table->decimal('total_withdrawn', 12, 2)->default(0);      // Lifetime payouts

            // Referral stats
            $table->integer('total_referrals')->default(0);
            $table->integer('successful_referrals')->default(0);

            $table->timestamp('last_earning_at')->nullable();
            $table->timestamp('last_withdrawal_at')->nullable();

            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('customer_wallets');
    }
};