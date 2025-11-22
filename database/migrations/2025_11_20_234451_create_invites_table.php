<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invites', function (Blueprint $table) {
            $table->id();
            $table->string('token')->unique(); // Unique invite token
            $table->foreignId('kid_id')->constrained()->onDelete('cascade'); // Which kid this invite is for
            $table->string('email')->nullable(); // Kid's email if using email invite
            $table->timestamp('expires_at'); // When invite expires (15 days)
            $table->timestamp('accepted_at')->nullable(); // When kid accepted (null until used)
            $table->string('status')->default('pending'); // pending, accepted, or expired
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invites');
    }
};
