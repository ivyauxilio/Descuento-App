<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('customer_wallet_transactions', function (Blueprint $table) {
            $table->id('transaction_id');
            $table->uuid('transaction_uuid')->unique();
            $table->unsignedBigInteger('user_id');

            $table->enum('type', [
                'referral_reward',  // Earned from referral
                'withdrawal',       // Cash out
                'adjustment',       // Manual by admin
                'reversal',         // Reversed by admin
                'purchase',         // Bought something
            ]);

            $table->decimal('amount', 12, 2); // + or -
            $table->decimal('balance_before', 12, 2);
            $table->decimal('balance_after', 12, 2);

            // Reference
            $table->foreignId('referral_id')->nullable()
                  ->constrained('referrals', 'referral_id')
                  ->onDelete('set null');

            // Withdrawal details
            $table->string('payment_method')->nullable();
            $table->string('payment_reference')->nullable();
            $table->enum('payment_status', ['pending', 'processing', 'paid', 'failed'])
                  ->nullable();

            $table->string('description')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->index(['user_id', 'created_at']);
            $table->index('type');
        });
    }

    public function down()
    {
        Schema::dropIfExists('customer_wallet_transactions');
    }
};