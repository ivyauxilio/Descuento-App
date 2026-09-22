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
        Schema::table('promotions', function (Blueprint $table) {
            $table->enum('voucher_type', ['basic', 'featured', 'priority'])
                  ->default('basic')
                  ->after('promo_type');
            $table->integer('credits_used')->default(1)->after('voucher_type');
            $table->boolean('is_boosted')->default(false)->after('credits_used');
            $table->timestamp('boosted_until')->nullable()->after('is_boosted');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('promotions', function (Blueprint $table) {
           $table->dropColumn(['voucher_type', 'credits_used', 'is_boosted', 'boosted_until']);
        });
    }
};