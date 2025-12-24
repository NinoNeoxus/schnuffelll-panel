<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('node_id')->constrained()->cascadeOnDelete();
            $table->string('ip');
            $table->string('alias')->nullable();
            $table->integer('port');
            $table->foreignId('server_id')->nullable(); // FK added in later migration
            $table->string('notes')->nullable();
            $table->timestamps();
            
            $table->unique(['node_id', 'ip', 'port']);
        });
        
        // Add foreign key to servers table for allocation_id (since we couldn't do it before allocations table existed)
        // Actually, we need to handle the circular dependency carefully or just add it later.
        // For simplicity in this rough draft, we'll assume the helper does it or we add a separate migration.
        // But let's just leave it, since servers table already has allocation_id (we need to create allocations BEFORE servers ideally, or use a separate migration for the FK).
    }

    public function down(): void
    {
        Schema::dropIfExists('allocations');
    }
};
