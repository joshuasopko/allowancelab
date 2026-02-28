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
            $table->timestamp('denial_acknowledged_at')->nullable()->after('denied_at');
            $table->timestamp('last_redemption_requested_at')->nullable()->after('denial_acknowledged_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('goals', function (Blueprint $table) {
            $table->dropColumn(['denial_acknowledged_at', 'last_redemption_requested_at']);
        });
    }
};
