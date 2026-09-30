<?php
// database/migrations/2024_01_04_000003_add_broadcast_id_to_notifications_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->unsignedBigInteger('broadcast_id')->nullable()->after('user_id');

            $table->foreign('broadcast_id')
                  ->references('broadcast_id')
                  ->on('notification_broadcasts')
                  ->onDelete('set null');

            $table->index('broadcast_id');
        });
    }

    public function down()
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropForeign(['broadcast_id']);
            $table->dropColumn('broadcast_id');
        });
    }
};