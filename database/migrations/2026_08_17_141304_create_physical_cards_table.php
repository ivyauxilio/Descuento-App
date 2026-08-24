<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create using raw SQL - this bypasses Laravel's foreign key checks
        DB::statement("
            CREATE TABLE `physical_cards` (
                `card_id` CHAR(36) NOT NULL,
                `user_id` BIGINT UNSIGNED NULL, 
                `card_number` VARCHAR(255) NOT NULL,
                `card_uid` VARCHAR(255) NULL,
                `qr_code` VARCHAR(255) NULL,
                `status` ENUM('active', 'inactive', 'lost', 'expired') NOT NULL DEFAULT 'active',
                `issued_at` TIMESTAMP NULL,
                `expires_at` TIMESTAMP NULL,
                `balance` INT NOT NULL DEFAULT 0,
                `points` INT NOT NULL DEFAULT 0,
                `created_at` TIMESTAMP NULL,
                `updated_at` TIMESTAMP NULL,
                `deleted_at` TIMESTAMP NULL,
                PRIMARY KEY (`card_id`),
                UNIQUE KEY `physical_cards_card_number_unique` (`card_number`),
                UNIQUE KEY `physical_cards_card_uid_unique` (`card_uid`),
                UNIQUE KEY `physical_cards_qr_code_unique` (`qr_code`),
                CONSTRAINT `physical_cards_user_id_foreign` 
                    FOREIGN KEY (`user_id`) 
                    REFERENCES `users`(`id`) 
                    ON DELETE CASCADE
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('physical_cards');
    }
};