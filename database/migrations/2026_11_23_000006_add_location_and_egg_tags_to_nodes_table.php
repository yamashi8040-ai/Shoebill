<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nodes', function (Blueprint $table) {
            $table->string('location')->nullable();
            // Pterodactyl 2.0 style: which egg tags may run here, and the deployment tags a reserved node requires.
            $table->json('egg_tags')->nullable();
            $table->json('reserved_tags')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('nodes', fn (Blueprint $table) => $table->dropColumn(['location', 'egg_tags', 'reserved_tags']));
    }
};
