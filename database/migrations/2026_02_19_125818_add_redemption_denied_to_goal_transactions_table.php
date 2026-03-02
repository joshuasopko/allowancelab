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
        $allTypes = ['auto_allocation', 'manual_deposit', 'manual_withdrawal', 'redemption', 'redemption_requested', 'redemption_denied'];

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE goal_transactions DROP CONSTRAINT IF EXISTS goal_transactions_transaction_type_check");
            $quoted = implode(', ', array_map(fn($t) => "'$t'::character varying", $allTypes));
            DB::statement("ALTER TABLE goal_transactions ADD CONSTRAINT goal_transactions_transaction_type_check CHECK (transaction_type::text = ANY (ARRAY[$quoted]::text[]))");
        } elseif (DB::getDriverName() === 'mysql') {
            $list = implode("', '", $allTypes);
            DB::statement("ALTER TABLE goal_transactions MODIFY COLUMN transaction_type ENUM('$list') NOT NULL");
        } elseif (DB::getDriverName() === 'sqlite') {
            // SQLite doesn't support ALTER COLUMN — use raw DDL to drop and re-add with new CHECK constraint
            $columns = Schema::getColumnListing('goal_transactions');
            if (in_array('transaction_type', $columns)) {
                $tempData = DB::table('goal_transactions')->select('id', 'transaction_type')->get();
                DB::statement('ALTER TABLE goal_transactions DROP COLUMN transaction_type');
            } else {
                $tempData = collect();
            }
            $typeList = implode("', '", $allTypes);
            DB::statement("ALTER TABLE goal_transactions ADD COLUMN transaction_type TEXT NOT NULL DEFAULT 'redemption' CHECK (transaction_type IN ('$typeList'))");
            foreach ($tempData as $data) {
                DB::table('goal_transactions')->where('id', $data->id)->update(['transaction_type' => $data->transaction_type]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $previousTypes = ['auto_allocation', 'manual_deposit', 'manual_withdrawal', 'redemption', 'redemption_requested'];

        // Remap any denied records back to redemption before removing the type
        DB::table('goal_transactions')
            ->where('transaction_type', 'redemption_denied')
            ->update(['transaction_type' => 'redemption']);

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE goal_transactions DROP CONSTRAINT IF EXISTS goal_transactions_transaction_type_check");
            $quoted = implode(', ', array_map(fn($t) => "'$t'::character varying", $previousTypes));
            DB::statement("ALTER TABLE goal_transactions ADD CONSTRAINT goal_transactions_transaction_type_check CHECK (transaction_type::text = ANY (ARRAY[$quoted]::text[]))");
        } elseif (DB::getDriverName() === 'mysql') {
            $list = implode("', '", $previousTypes);
            DB::statement("ALTER TABLE goal_transactions MODIFY COLUMN transaction_type ENUM('$list') NOT NULL");
        } elseif (DB::getDriverName() === 'sqlite') {
            $columns = Schema::getColumnListing('goal_transactions');
            if (in_array('transaction_type', $columns)) {
                $tempData = DB::table('goal_transactions')->select('id', 'transaction_type')->get();
                DB::statement('ALTER TABLE goal_transactions DROP COLUMN transaction_type');
            } else {
                $tempData = collect();
            }
            $typeList = implode("', '", $previousTypes);
            DB::statement("ALTER TABLE goal_transactions ADD COLUMN transaction_type TEXT NOT NULL DEFAULT 'redemption' CHECK (transaction_type IN ('$typeList'))");
            foreach ($tempData as $data) {
                DB::table('goal_transactions')->where('id', $data->id)->update(['transaction_type' => $data->transaction_type]);
            }
        }
    }
};
