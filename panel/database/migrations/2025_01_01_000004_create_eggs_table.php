<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eggs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nest_id')->constrained()->cascadeOnDelete();
            $table->string('author');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('docker_image');
            $table->text('startup_command');
            $table->json('config')->nullable(); // Env variables config, etc
            $table->string('config_files')->nullable();
            $table->string('config_startup')->nullable();
            $table->string('config_logs')->nullable();
            $table->string('config_stop')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eggs');
    }
};
