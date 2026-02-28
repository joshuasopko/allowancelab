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
        Schema::table('goals', function (Blueprint $table) {
            $table->text('denial_reason')->nullable()->after('redeemed_by_user_id');
            $table->timestamp('denied_at')->nullable()->after('denial_reason');
        });
    }

    public function down(): void
    {
        Schema::table('goals', function (Blueprint $table) {
            $table->dropColumn(['denial_reason', 'denied_at']);
        });
    }
};
