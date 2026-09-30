<?php
// database/migrations/2024_01_03_000003_create_referrals_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('referrals', function (Blueprint $table) {
            $table->id('referral_id');
            $table->uuid('referral_uuid')->unique();

            // Who referred
            $table->unsignedBigInteger('referrer_id');

            // Who was referred
            $table->unsignedBigInteger('referee_id');

            // Referral code used
            $table->string('referral_code', 20);

            // Reward
            $table->decimal('reward_amount', 12, 2)->default(50.00);
            $table->string('currency', 3)->default('PHP');

            // Status flow
            $table->enum('status', [
                'pending',      // Signup happened, waiting for purchase
                'qualified',    // Purchase made, waiting for admin approval
                'approved',     // Admin approved, credited to wallet
                'rejected',     // Fraud / invalid
                'expired',      // Qualified but not approved in time
            ])->default('pending');

            // When did the referred user make a qualifying purchase?
            $table->string('purchase_reference')->nullable(); // Order ID / card serial
            $table->decimal('purchase_amount', 12, 2)->nullable();
            $table->timestamp('purchase_verified_at')->nullable();

            // Admin approval
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();

            // Fraud prevention
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->string('device_fingerprint')->nullable();

            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->foreign('referrer_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('referee_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('approved_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            // One referee = one referral (no duplicates)
            $table->unique('referee_id');

            $table->index(['referrer_id', 'status']);
            $table->index('referral_code');
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('referrals');
    }
};