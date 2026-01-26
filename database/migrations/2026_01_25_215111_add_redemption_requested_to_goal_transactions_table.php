<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For PostgreSQL (production), modify the enum
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TYPE goal_transaction_type ADD VALUE 'redemption_requested'");
        }
        // For MySQL (if ever used)
        elseif (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE goal_transactions MODIFY COLUMN transaction_type ENUM('auto_allocation', 'manual_deposit', 'manual_withdrawal', 'redemption', 'redemption_requested') NOT NULL");
        }
        // For SQLite, we need to recreate the column with the new enum values
        elseif (DB::getDriverName() === 'sqlite') {
            // SQLite doesn't support ALTER COLUMN, so we need to recreate the column
            Schema::table('goal_transactions', function (Blueprint $table) {
                $table->dropColumn('transaction_type');
            });
            Schema::table('goal_transactions', function (Blueprint $table) {
                $table->enum('transaction_type', ['auto_allocation', 'manual_deposit', 'manual_withdrawal', 'redemption', 'redemption_requested']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Update any redemption_requested transactions to redemption
        DB::statement("UPDATE goal_transactions SET transaction_type = 'redemption' WHERE transaction_type = 'redemption_requested'");

        // For PostgreSQL and MySQL, we can't easily remove enum values without recreating the column
        // Since this is a one-way migration in practice, leaving the enum value is acceptable
    }
};
