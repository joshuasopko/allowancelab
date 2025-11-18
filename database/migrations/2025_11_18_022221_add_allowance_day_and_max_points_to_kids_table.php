<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('kids', function (Blueprint $table) {
            $table->string('allowance_day')->default('friday');
            $table->integer('max_points')->default(10);
        });
    }

    public function down(): void
    {
        Schema::table('kids', function (Blueprint $table) {
            $table->dropColumn(['allowance_day', 'max_points']);
        });
    }
};