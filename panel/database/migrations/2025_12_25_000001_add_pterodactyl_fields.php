<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add new fields to users and nodes tables for Pterodactyl parity.
     */
    public function up(): void
    {
        // Add new user fields
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->after('name');
            $table->string('name_first')->nullable()->after('username');
            $table->string('name_last')->nullable()->after('name_first');
            $table->string('external_id')->nullable()->unique()->after('id');
        });

        // Add new node fields
        Schema::table('nodes', function (Blueprint $table) {
            $table->char('uuid', 36)->unique()->nullable()->after('id');
            $table->text('description')->nullable()->after('name');
            $table->string('daemon_base')->default('/var/lib/pterodactyl/volumes')->after('daemon_sftp');
            $table->unsignedInteger('upload_size')->default(100)->after('daemon_base');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['username', 'name_first', 'name_last', 'external_id']);
        });

        Schema::table('nodes', function (Blueprint $table) {
            $table->dropColumn(['uuid', 'description', 'daemon_base', 'upload_size']);
        });
    }
};
