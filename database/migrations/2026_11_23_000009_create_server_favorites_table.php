<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('server_favorites', function (Blueprint $table) {
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('server_id');
            $table->timestamp('created_at')->useCurrent();

            $table->primary(['user_id', 'server_id']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('server_id')->references('id')->on('servers')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('server_favorites');
    }
};
