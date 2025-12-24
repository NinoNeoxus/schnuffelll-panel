<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('eggs', function (Blueprint $table) {
            // Add new columns if they don't exist
            if (!Schema::hasColumn('eggs', 'uuid')) {
                $table->uuid('uuid')->nullable()->after('nest_id');
            }
            if (!Schema::hasColumn('eggs', 'features')) {
                $table->json('features')->nullable()->after('description');
            }
            if (!Schema::hasColumn('eggs', 'docker_images')) {
                $table->json('docker_images')->nullable()->after('features');
            }
            if (!Schema::hasColumn('eggs', 'startup')) {
                $table->text('startup')->nullable()->after('docker_images');
            }
            if (!Schema::hasColumn('eggs', 'script_install')) {
                $table->longText('script_install')->nullable()->after('config_stop');
            }
            if (!Schema::hasColumn('eggs', 'script_container')) {
                $table->string('script_container')->nullable()->after('script_install');
            }
            if (!Schema::hasColumn('eggs', 'script_entry')) {
                $table->string('script_entry')->nullable()->after('script_container');
            }
        });
    }

    public function down(): void
    {
        Schema::table('eggs', function (Blueprint $table) {
            $table->dropColumn([
                'uuid', 'features', 'docker_images', 'startup',
                'script_install', 'script_container', 'script_entry'
            ]);
        });
    }
};
