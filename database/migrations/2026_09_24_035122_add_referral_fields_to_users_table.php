<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('referral_code', 20)->unique()->nullable()->after('uuid');
            $table->unsignedBigInteger('referred_by')->nullable()->after('referral_code');
            $table->timestamp('referred_at')->nullable()->after('referred_by');
            $table->integer('total_referrals')->default(0)->after('referred_at');
            $table->decimal('total_referral_earnings', 12, 2)->default(0)->after('total_referrals');

            $table->foreign('referred_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->index('referral_code');
            $table->index('referred_by');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['referred_by']);
            $table->dropColumn([
                'referral_code',
                'referred_by',
                'referred_at',
                'total_referrals',
                'total_referral_earnings',
            ]);
        });
    }
};