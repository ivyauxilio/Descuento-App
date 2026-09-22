<?php
// database/migrations/2024_01_01_000003_create_credit_transactions_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('credit_transactions', function (Blueprint $table) {
            $table->id('transaction_id');
            $table->uuid('transaction_uuid')->unique();
            $table->uuid('merchant_id');
            
            // Transaction type
            $table->enum('type', [
                'purchase',      // Bought a plan
                'usage',         // Used credits for a promo
                'refund',        // Refunded
                'bonus',         // Free credits
                'expiry',        // Credits expired
                'adjustment',    // Manual adjustment
            ]);
            
            // Credit change
            $table->integer('credits'); // + for add, - for deduct
            $table->integer('balance_before');
            $table->integer('balance_after');
            
            // Money
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('currency', 3)->default('PHP');
            
            // Reference
            $table->foreignId('plan_id')->nullable()
                  ->constrained('subscription_plans', 'plan_id')
                  ->onDelete('set null');
            $table->uuid('promotion_id')->nullable();
            $table->string('reference_number')->nullable(); // For receipts
            
            // Details
            $table->string('description')->nullable();
            $table->json('metadata')->nullable();
            
            // Payment info
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])
                  ->nullable();
            $table->string('payment_method')->nullable(); // gcash, card, etc
            $table->string('payment_reference')->nullable(); // GCash ref number
            
            $table->timestamps();
            
            $table->foreign('merchant_id')
                  ->references('merchant_id')
                  ->on('merchants')
                  ->onDelete('cascade');
                  
            $table->index(['merchant_id', 'created_at']);
            $table->index('type');
            $table->index('payment_status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('credit_transactions');
    }
};