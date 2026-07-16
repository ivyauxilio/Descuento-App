<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('redemptions', function (Blueprint $table) {
            $table->uuid('redemption_id')->primary();
            $table->unsignedBigInteger('customer_id');
            $table->uuid('promotion_id');
            $table->uuid('order_id')->nullable();
            $table->timestamp('redeemed_at');
            $table->timestamp('expires_at')->nullable();
            $table->enum('status', ['pending', 'used', 'expired'])->default('pending');
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('promotion_id')->references('promotion_id')->on('promotions')->onDelete('cascade');
            $table->foreign('order_id')->references('order_id')->on('orders')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('redemptions');
    }
};