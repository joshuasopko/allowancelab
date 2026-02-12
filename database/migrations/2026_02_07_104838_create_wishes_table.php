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
        Schema::create('wishes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_id')->constrained()->onDelete('cascade');
            $table->foreignId('kid_id')->constrained('kids')->onDelete('cascade');
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->onDelete('set null');

            // Wish Details
            $table->string('item_name');
            $table->text('item_url')->nullable();
            $table->string('image_path')->nullable(); // Scraped or manually uploaded
            $table->decimal('price', 10, 2);
            $table->text('reason')->nullable();

            // Status tracking
            $table->enum('status', [
                'saved',              // Kid saved it to wish list
                'pending_approval',   // Kid asked parent to buy
                'approved',           // Parent approved purchase
                'declined',           // Parent declined
                'purchased',          // Parent completed purchase
                'cancelled'           // Wish was cancelled/deleted
            ])->default('saved');

            // Timestamps for workflow
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('last_reminded_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('purchased_at')->nullable();
            $table->foreignId('purchased_by_user_id')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();
            $table->softDeletes(); // Allow recovery of deleted wishes

            // Indexes
            $table->index(['kid_id', 'status']);
            $table->index(['family_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wishes');
    }
};
