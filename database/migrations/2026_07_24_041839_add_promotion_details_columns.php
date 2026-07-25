<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('promotions', function (Blueprint $table) {
            // For Buy X Get Y
            $table->integer('buy_quantity')->nullable()->after('value');
            $table->integer('get_quantity')->nullable()->after('buy_quantity');
            $table->decimal('get_discount_percentage', 5, 2)->nullable()->after('get_quantity');
            
            // For Tiered Discount
            $table->json('tiers')->nullable()->after('get_discount_percentage');
            
            // For Free Gift
            $table->uuid('free_gift_product_id')->nullable()->after('tiers');
            
            // For Loyalty Points
            $table->integer('points_multiplier')->nullable()->after('status');
            
            // General
            $table->text('description')->nullable()->after('title');
            $table->decimal('max_discount_amount', 10, 2)->nullable()->after('min_quantity');
            $table->integer('priority')->default(0)->after('status');
            $table->boolean('is_stackable')->default(false)->after('priority');
            
            // Foreign key for free gift
            $table->foreign('free_gift_product_id')
                  ->references('menu_item_id')
                  ->on('menu_items')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('promotions', function (Blueprint $table) {
            $table->dropForeign(['free_gift_product_id']);
            $table->dropColumn([
                'buy_quantity',
                'get_quantity',
                'get_discount_percentage',
                'tiers',
                'free_gift_product_id',
                'points_multiplier',
                'description',
                'max_discount_amount',
                'priority',
                'is_stackable',
            ]);
        });
    }
};