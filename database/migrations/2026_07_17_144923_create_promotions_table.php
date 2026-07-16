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
        Schema::create('promotions', function (Blueprint $table) {
            $table->uuid('promotion_id')->primary();
            $table->uuid('merchant_id');
            $table->uuid('category_id')->nullable();
            $table->uuid('free_menu_item_id')->nullable();
            $table->uuid('required_menu_item_id')->nullable();
            $table->string('title');
            $table->enum('promo_type', ['percentage', 'fixed', 'bogo']);
            $table->decimal('value', 10, 2)->default(0);
            $table->decimal('min_order_amount', 10, 2)->nullable();
            $table->integer('min_quantity')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['active', 'inactive', 'expired'])->default('active');
            $table->timestamps();

            $table->foreign('merchant_id')->references('merchant_id')->on('merchants')->onDelete('cascade');
            $table->foreign('category_id')->references('category_id')->on('categories')->onDelete('set null');
            $table->foreign('free_menu_item_id')->references('menu_item_id')->on('menu_items')->onDelete('set null');
            $table->foreign('required_menu_item_id')->references('menu_item_id')->on('menu_items')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};