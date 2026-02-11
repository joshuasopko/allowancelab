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
        Schema::create('wish_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wish_id')->constrained()->onDelete('cascade');
            $table->foreignId('kid_id')->constrained('kids')->onDelete('cascade');
            $table->foreignId('family_id')->constrained()->onDelete('cascade');
            $table->foreignId('performed_by_user_id')->nullable()->constrained('users')->onDelete('set null');

            $table->enum('transaction_type', [
                'created',
                'requested',
                'reminded',
                'approved',
                'declined',
                'purchased',
                'cancelled'
            ]);

            $table->text('note')->nullable();
            $table->timestamp('created_at');

            $table->index(['wish_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wish_transactions');
    }
};
