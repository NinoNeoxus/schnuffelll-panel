<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('egg_variables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('egg_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('env_variable');
            $table->text('default_value')->nullable();
            $table->boolean('user_viewable')->default(true);
            $table->boolean('user_editable')->default(false);
            $table->string('rules')->default('required|string');
            $table->integer('sort')->default(0);
            $table->timestamps();
            
            $table->unique(['egg_id', 'env_variable']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('egg_variables');
    }
};
