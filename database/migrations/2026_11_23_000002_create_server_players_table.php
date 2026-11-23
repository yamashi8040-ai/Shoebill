<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Every player name a game query has reported, with play time counted in sample minutes.
        Schema::create('server_players', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('server_id');
            $table->string('name');
            $table->timestamp('first_seen_at');
            $table->timestamp('last_seen_at');
            $table->unsignedInteger('minutes_played')->default(0);

            $table->unique(['server_id', 'name']);
            $table->index(['server_id', 'last_seen_at']);
            $table->foreign('server_id')->references('id')->on('servers')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('server_players');
    }
};
