<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('external_id')->nullable()->unique();
            $table->string('uuid')->unique();
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->string('name_first');
            $table->string('name_last');
            $table->string('name')->nullable();
            $table->string('password');
            $table->string('language')->default('en');
            $table->boolean('root_admin')->default(false);
            $table->boolean('use_totp')->default(false);
            $table->text('totp_secret')->nullable();
            $table->timestamp('totp_authenticated_at')->nullable();
            $table->timestamp('gravatar')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
