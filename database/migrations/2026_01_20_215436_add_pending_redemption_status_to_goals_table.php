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
        // For PostgreSQL (production), modify the check constraint
        if (DB::getDriverName() === 'pgsql') {
            // First, drop the existing check constraint
            DB::statement("ALTER TABLE goals DROP CONSTRAINT IF EXISTS goals_status_check");

            // Add new check constraint with the additional value
            DB::statement("ALTER TABLE goals ADD CONSTRAINT goals_status_check CHECK (status::text = ANY (ARRAY['active'::character varying, 'ready_to_redeem'::character varying, 'pending_redemption'::character varying, 'redeemed'::character varying]::text[]))");
        }
        // For MySQL (if ever used)
        elseif (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE goals MODIFY COLUMN status ENUM('active', 'ready_to_redeem', 'pending_redemption', 'redeemed') DEFAULT 'active'");
        }
        // For SQLite (local development/testing)
        elseif (DB::getDriverName() === 'sqlite') {
            // Check if we need to drop the column first (in case of rollback/re-run)
            $columns = Schema::getColumnListing('goals');
            if (in_array('status', $columns)) {
                // Store existing data temporarily
                $tempData = DB::table('goals')->select('id', 'status')->get();

                Schema::table('goals', function (Blueprint $table) {
                    $table->dropColumn('status');
                });

                Schema::table('goals', function (Blueprint $table) {
                    $table->enum('status', ['active', 'ready_to_redeem', 'pending_redemption', 'redeemed'])->default('active')->after('expected_completion_date');
                });

                // Restore existing data
                foreach ($tempData as $data) {
                    DB::table('goals')->where('id', $data->id)->update(['status' => $data->status]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Check if the column exists before attempting to update
        $columns = Schema::getColumnListing('goals');
        if (in_array('status', $columns)) {
            // Update any pending_redemption goals back to ready_to_redeem
            DB::table('goals')
                ->where('status', 'pending_redemption')
                ->update(['status' => 'ready_to_redeem']);
        }

        // For PostgreSQL, restore the old check constraint
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE goals DROP CONSTRAINT IF EXISTS goals_status_check");
            DB::statement("ALTER TABLE goals ADD CONSTRAINT goals_status_check CHECK (status::text = ANY (ARRAY['active'::character varying, 'ready_to_redeem'::character varying, 'redeemed'::character varying]::text[]))");
        }
        // For MySQL
        elseif (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE goals MODIFY COLUMN status ENUM('active', 'ready_to_redeem', 'redeemed') DEFAULT 'active'");
        }
        // For SQLite
        elseif (DB::getDriverName() === 'sqlite') {
            if (in_array('status', $columns)) {
                $tempData = DB::table('goals')->select('id', 'status')->get();

                Schema::table('goals', function (Blueprint $table) {
                    $table->dropColumn('status');
                });

                Schema::table('goals', function (Blueprint $table) {
                    $table->enum('status', ['active', 'ready_to_redeem', 'redeemed'])->default('active')->after('expected_completion_date');
                });

                foreach ($tempData as $data) {
                    DB::table('goals')->where('id', $data->id)->update(['status' => $data->status]);
                }
            }
        }
    }
};
