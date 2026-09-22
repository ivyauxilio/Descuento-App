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
        Schema::table('merchant_wallets', function (Blueprint $table) {
            $table->boolean('welcome_bonus_claimed')->default(false)->after('total_spent');
            $table->timestamp('welcome_bonus_claimed_at')->nullable()->after('welcome_bonus_claimed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('merchant_wallets', function (Blueprint $table) {
            $table->dropColumn(['welcome_bonus_claimed', 'welcome_bonus_claimed_at']);
        });
    }
};