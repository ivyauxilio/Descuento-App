<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Drop foreign key if it exists using raw SQL (MySQL 8.0+)
        try {
            DB::statement('ALTER TABLE qr_code_usages DROP FOREIGN KEY IF EXISTS qr_code_usages_card_id_foreign');
        } catch (\Exception $e) {
            // If the above fails, try without IF EXISTS
            try {
                DB::statement('ALTER TABLE qr_code_usages DROP FOREIGN KEY qr_code_usages_card_id_foreign');
            } catch (\Exception $e2) {
                // Foreign key doesn't exist, continue
            }
        }

        // Add columns
        $this->addColumnIfNotExists('qr_code_usages', 'card_id', 'CHAR(36) NULL');
        $this->addColumnIfNotExists('qr_code_usages', 'card_number', 'VARCHAR(255) NULL');
        $this->addColumnIfNotExists('qr_code_usages', 'redemption_method', "ENUM('app_qr', 'card_scan', 'nfc', 'manual') DEFAULT 'app_qr'");
        $this->addColumnIfNotExists('qr_code_usages', 'status', "ENUM('pending', 'completed', 'failed', 'reversed') DEFAULT 'completed'");
        $this->addColumnIfNotExists('qr_code_usages', 'amount_paid', 'DECIMAL(10, 2) NULL');
        $this->addColumnIfNotExists('qr_code_usages', 'points_earned', 'INT DEFAULT 0');
        $this->addColumnIfNotExists('qr_code_usages', 'metadata', 'JSON NULL');

        // Add foreign key
        $this->addForeignKeyIfNotExists('qr_code_usages', 'card_id', 'physical_cards', 'card_id');

        // Add indexes
        $this->addIndexIfNotExists('qr_code_usages', 'card_id');
        $this->addIndexIfNotExists('qr_code_usages', 'card_number');
        $this->addIndexIfNotExists('qr_code_usages', 'redemption_method');
        $this->addIndexIfNotExists('qr_code_usages', 'status');

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Drop foreign keys
        try {
            DB::statement('ALTER TABLE qr_code_usages DROP FOREIGN KEY IF EXISTS qr_code_usages_card_id_foreign');
        } catch (\Exception $e) {
            // Foreign key might not exist
        }

        $columns = ['card_id', 'card_number', 'redemption_method', 'status', 'amount_paid', 'points_earned', 'metadata'];
        foreach ($columns as $column) {
            $this->dropColumnIfExists('qr_code_usages', $column);
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    private function addColumnIfNotExists($table, $column, $definition): void
    {
        $result = DB::select("
            SELECT COLUMN_NAME 
            FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = ? 
            AND COLUMN_NAME = ?
        ", [$table, $column]);

        if (empty($result)) {
            try {
                DB::statement("ALTER TABLE $table ADD COLUMN $column $definition");
            } catch (\Exception $e) {
                // Column might already exist
            }
        }
    }

    private function dropColumnIfExists($table, $column): void
    {
        $result = DB::select("
            SELECT COLUMN_NAME 
            FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = ? 
            AND COLUMN_NAME = ?
        ", [$table, $column]);

        if (!empty($result)) {
            try {
                DB::statement("ALTER TABLE $table DROP COLUMN $column");
            } catch (\Exception $e) {
                // Column might not exist
            }
        }
    }

    private function addForeignKeyIfNotExists($table, $column, $referencesTable, $referencesColumn): void
    {
        $constraintName = $table . '_' . $column . '_foreign';
        
        // Check if foreign key exists
        $result = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = ? 
            AND COLUMN_NAME = ?
        ", [$table, $column]);

        if (empty($result)) {
            try {
                DB::statement("
                    ALTER TABLE $table 
                    ADD CONSTRAINT $constraintName 
                    FOREIGN KEY ($column) 
                    REFERENCES $referencesTable($referencesColumn) 
                    ON DELETE SET NULL
                ");
            } catch (\Exception $e) {
                // Foreign key might already exist
            }
        }
    }

    private function addIndexIfNotExists($table, $column): void
    {
        $result = DB::select("
            SELECT INDEX_NAME 
            FROM INFORMATION_SCHEMA.STATISTICS 
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = ? 
            AND COLUMN_NAME = ?
        ", [$table, $column]);

        if (empty($result)) {
            $indexName = $table . '_' . $column . '_index';
            try {
                DB::statement("ALTER TABLE $table ADD INDEX $indexName ($column)");
            } catch (\Exception $e) {
                // Index might already exist
            }
        }
    }
};