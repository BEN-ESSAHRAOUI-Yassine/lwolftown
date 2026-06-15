<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->string('nickname');
            $table->string('session_token')->unique();
            $table->foreignId('role_id')->nullable()->constrained('roles')->nullOnDelete();
            $table->boolean('is_alive')->default(true);
            $table->boolean('is_host')->default(false);
            $table->boolean('is_narrator')->default(false);
            $table->boolean('voting_banned')->default(false);
            $table->boolean('is_silenced')->default(false);
            $table->boolean('is_slave')->default(false);
            $table->unsignedBigInteger('master_id')->nullable();
            $table->integer('seat_position')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('players');
    }
};
