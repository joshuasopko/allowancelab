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
            DB::statement("ALTER TYPE goal_status ADD VALUE 'pending_redemption' BEFORE 'redeemed'");
        }
        // For MySQL (if ever used)
        elseif (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE goals MODIFY COLUMN status ENUM('active', 'ready_to_redeem', 'pending_redemption', 'redeemed') DEFAULT 'active'");
        }
        // For SQLite, we need to recreate the table with the new enum values
        elseif (DB::getDriverName() === 'sqlite') {
            // SQLite doesn't support ALTER COLUMN, so we need to recreate the table
            Schema::table('goals', function (Blueprint $table) {
                $table->dropColumn('status');
            });
            Schema::table('goals', function (Blueprint $table) {
                $table->enum('status', ['active', 'ready_to_redeem', 'pending_redemption', 'redeemed'])->default('active');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Update any pending_redemption goals back to ready_to_redeem
        DB::statement("UPDATE goals SET status = 'ready_to_redeem' WHERE status = 'pending_redemption'");

        // For PostgreSQL and MySQL, we can't easily remove enum values without recreating the column
        // Since this is a one-way migration in practice, leaving the enum value is acceptable
    }
};
