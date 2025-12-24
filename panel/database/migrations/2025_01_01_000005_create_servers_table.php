<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('servers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('uuidShort')->unique();
            $table->foreignId('node_id')->constrained();
            $table->foreignId('owner_id')->constrained('users');
            $table->foreignId('egg_id')->constrained();
            $table->foreignId('allocation_id')->nullable()->constrained('allocations'); // Primary allocation
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('suspended')->default(false);
            $table->integer('memory')->default(0);
            $table->integer('swap')->default(0);
            $table->integer('disk')->default(0);
            $table->integer('io')->default(500);
            $table->integer('cpu')->default(0); // 0 = unlimited
            $table->string('status')->nullable(); // installing, running, etc
            $table->string('image')->nullable(); // Docker image override
            $table->text('startup')->nullable(); // Startup cmd override
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('servers');
    }
};
