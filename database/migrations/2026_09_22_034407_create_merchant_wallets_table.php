<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('merchant_wallets', function (Blueprint $table) {
            $table->id('wallet_id');
            $table->uuid('merchant_id');
            
            // Credit balance
            $table->integer('credit_balance')->default(0);
            $table->integer('total_credits_purchased')->default(0);
            $table->integer('total_credits_used')->default(0);
            
            // Money
            $table->decimal('total_spent', 12, 2)->default(0);
            
            // Timestamps
            $table->timestamp('last_purchase_at')->nullable();
            $table->timestamp('last_used_at')->nullable();
            
            $table->timestamps();
            
            $table->foreign('merchant_id')
                  ->references('merchant_id')
                  ->on('merchants')
                  ->onDelete('cascade');
                  
            $table->index('merchant_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('merchant_wallets');
    }
};