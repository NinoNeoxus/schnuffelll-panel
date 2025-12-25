<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nodes', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('location_id')->constrained()->cascadeOnDelete();
            $table->boolean('public')->default(true);
            $table->string('fqdn');
            $table->string('scheme')->default('https');
            $table->integer('behind_proxy')->default(0);
            $table->integer('memory')->default(0);
            $table->integer('memory_overallocate')->default(0);
            $table->integer('disk')->default(0);
            $table->integer('disk_overallocate')->default(0);
            $table->string('daemon_token_id')->nullable();
            $table->text('daemon_token')->nullable();
            $table->integer('daemon_listen')->default(8080);
            $table->integer('daemon_sftp')->default(2022);
            $table->string('daemon_base')->default('/var/lib/pterodactyl/volumes');
            $table->unsignedInteger('upload_size')->default(100);
            $table->boolean('maintenance_mode')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nodes');
    }
};
