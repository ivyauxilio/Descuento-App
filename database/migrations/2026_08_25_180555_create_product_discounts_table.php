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
        Schema::create('product_discounts', function (Blueprint $table) {
            $table->id('discount_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('merchant_id');
            $table->enum('type', ['percentage', 'fixed', 'bogo']);
            $table->decimal('value', 10, 2);
            $table->decimal('min_quantity', 10, 2)->default(1);
            $table->integer('max_quantity')->nullable();
            $table->datetime('start_date');
            $table->datetime('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('usage_limit')->nullable();
            $table->integer('used_count')->default(0);
            $table->json('applicable_customers')->nullable();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_discounts');
    }
};