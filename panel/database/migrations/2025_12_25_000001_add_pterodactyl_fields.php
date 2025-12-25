<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add new fields to nodes table for Pterodactyl parity.
     * Note: User fields are now in create_users_table migration.
     */
    public function up(): void
    {
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
        Schema::table('nodes', function (Blueprint $table) {
            $table->dropColumn(['uuid', 'description', 'daemon_base', 'upload_size']);
        });
    }
};
