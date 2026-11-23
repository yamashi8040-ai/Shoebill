<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // One row per server per minute, kept for 31 days (see shoebill:stats:rollup).
        Schema::create('server_stat_samples', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('server_id');
            $table->float('cpu')->default(0);
            $table->unsignedBigInteger('memory_bytes')->default(0);
            $table->unsignedInteger('players')->nullable();
            $table->unsignedInteger('max_players')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['server_id', 'created_at']);
            $table->foreign('server_id')->references('id')->on('servers')->cascadeOnDelete();
        });

        // Per-day sums so the all-time average survives sample pruning.
        Schema::create('server_stat_daily', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('server_id');
            $table->date('date');
            $table->unsignedInteger('samples');
            $table->double('cpu_sum');
            $table->double('memory_sum');
            $table->unsignedInteger('player_samples')->default(0);
            $table->unsignedBigInteger('players_sum')->default(0);
            $table->unsignedInteger('players_peak')->nullable();

            $table->unique(['server_id', 'date']);
            $table->foreign('server_id')->references('id')->on('servers')->cascadeOnDelete();
        });

        Schema::table('eggs', function (Blueprint $table) {
            $table->string('query_protocol')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('server_stat_samples');
        Schema::dropIfExists('server_stat_daily');
        Schema::table('eggs', fn (Blueprint $table) => $table->dropColumn('query_protocol'));
    }
};
