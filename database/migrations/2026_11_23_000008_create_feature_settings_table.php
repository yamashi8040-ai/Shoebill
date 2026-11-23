<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A missing row means "inherit": server -> node -> global -> on.
        Schema::create('feature_settings', function (Blueprint $table) {
            $table->id();
            $table->string('feature');
            $table->string('scope'); // global, node or server
            $table->unsignedInteger('scope_id')->default(0);
            $table->boolean('enabled');

            $table->unique(['feature', 'scope', 'scope_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feature_settings');
    }
};
