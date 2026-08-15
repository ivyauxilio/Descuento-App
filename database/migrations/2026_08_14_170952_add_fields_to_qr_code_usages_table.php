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
        Schema::table('qr_code_usages', function (Blueprint $table) {
            $table->unsignedBigInteger('redeemed_by')->nullable()->after('user_id');
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending')->after('scanned_at');

            $table->foreign('redeemed_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('qr_code_usages', function (Blueprint $table) {
            $table->dropForeign(['redeemed_by']);
            $table->dropColumn(['redeemed_by', 'status']);
        });
    }
};