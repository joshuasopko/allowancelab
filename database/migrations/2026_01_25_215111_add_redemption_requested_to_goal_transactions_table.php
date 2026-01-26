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
        // For PostgreSQL (production), we need to modify the check constraint
        if (DB::getDriverName() === 'pgsql') {
            // First, drop the existing check constraint
            DB::statement("ALTER TABLE goal_transactions DROP CONSTRAINT IF EXISTS goal_transactions_transaction_type_check");

            // Add new check constraint with the additional value
            DB::statement("ALTER TABLE goal_transactions ADD CONSTRAINT goal_transactions_transaction_type_check CHECK (transaction_type::text = ANY (ARRAY['auto_allocation'::character varying, 'manual_deposit'::character varying, 'manual_withdrawal'::character varying, 'redemption'::character varying, 'redemption_requested'::character varying]::text[]))");
        }
        // For MySQL (if ever used)
        elseif (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE goal_transactions MODIFY COLUMN transaction_type ENUM('auto_allocation', 'manual_deposit', 'manual_withdrawal', 'redemption', 'redemption_requested') NOT NULL");
        }
        // For SQLite (local development/testing)
        elseif (DB::getDriverName() === 'sqlite') {
            // Check if we need to drop the column first (in case of rollback/re-run)
            $columns = Schema::getColumnListing('goal_transactions');
            if (in_array('transaction_type', $columns)) {
                // Store existing data temporarily
                $tempData = DB::table('goal_transactions')->select('id', 'transaction_type')->get();

                Schema::table('goal_transactions', function (Blueprint $table) {
                    $table->dropColumn('transaction_type');
                });

                Schema::table('goal_transactions', function (Blueprint $table) {
                    $table->enum('transaction_type', ['auto_allocation', 'manual_deposit', 'manual_withdrawal', 'redemption', 'redemption_requested'])->after('amount');
                });

                // Restore existing data
                foreach ($tempData as $data) {
                    DB::table('goal_transactions')->where('id', $data->id)->update(['transaction_type' => $data->transaction_type]);
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
        $columns = Schema::getColumnListing('goal_transactions');
        if (in_array('transaction_type', $columns)) {
            // Update any redemption_requested transactions to redemption
            DB::table('goal_transactions')
                ->where('transaction_type', 'redemption_requested')
                ->update(['transaction_type' => 'redemption']);
        }

        // For PostgreSQL, restore the old check constraint
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE goal_transactions DROP CONSTRAINT IF EXISTS goal_transactions_transaction_type_check");
            DB::statement("ALTER TABLE goal_transactions ADD CONSTRAINT goal_transactions_transaction_type_check CHECK (transaction_type::text = ANY (ARRAY['auto_allocation'::character varying, 'manual_deposit'::character varying, 'manual_withdrawal'::character varying, 'redemption'::character varying]::text[]))");
        }
        // For MySQL
        elseif (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE goal_transactions MODIFY COLUMN transaction_type ENUM('auto_allocation', 'manual_deposit', 'manual_withdrawal', 'redemption') NOT NULL");
        }
        // For SQLite
        elseif (DB::getDriverName() === 'sqlite') {
            if (in_array('transaction_type', $columns)) {
                $tempData = DB::table('goal_transactions')->select('id', 'transaction_type')->get();

                Schema::table('goal_transactions', function (Blueprint $table) {
                    $table->dropColumn('transaction_type');
                });

                Schema::table('goal_transactions', function (Blueprint $table) {
                    $table->enum('transaction_type', ['auto_allocation', 'manual_deposit', 'manual_withdrawal', 'redemption'])->after('amount');
                });

                foreach ($tempData as $data) {
                    DB::table('goal_transactions')->where('id', $data->id)->update(['transaction_type' => $data->transaction_type]);
                }
            }
        }
    }
};
