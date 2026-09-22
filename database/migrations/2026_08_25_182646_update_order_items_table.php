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
        Schema::table('order_items', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id')->nullable()->after('menu_item_id');
            $table->uuid('promotion_id')->nullable()->after('product_id');

            $table->decimal('discount_applied', 10, 2)
                ->default(0)
                ->after('subtotal');

            $table->decimal('points_earned', 10, 2)
                ->default(0)
                ->after('discount_applied');

            $table->json('customizations')->nullable()->after('points_earned');
            $table->json('addons')->nullable()->after('customizations');
            $table->string('unit')->default('piece')->after('addons');
            $table->decimal('unit_value', 10, 2)->nullable()->after('unit');
            $table->decimal('weight', 10, 2)->nullable()->after('unit_value');
            $table->text('special_instructions')->nullable()->after('weight');
            $table->boolean('is_redeemed_with_points')
                ->default(false)
                ->after('special_instructions');

            $table->foreign('product_id')
                ->references('product_id')
                ->on('products')
                ->nullOnDelete();

            $table->foreign('promotion_id')
                ->references('promotion_id')
                ->on('promotions')
                ->nullOnDelete();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropForeign(['promotion_id']);
            $table->dropColumn([
                'product_id',
                'promotion_id',
                'discount_applied',
                'points_earned',
                'customizations',
                'addons',
                'unit',
                'unit_value',
                'weight',
                'special_instructions',
                'is_redeemed_with_points'
            ]);
        });
    }
};