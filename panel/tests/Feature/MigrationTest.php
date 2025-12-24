<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Artisan;

class MigrationTest extends TestCase
{
    /**
     * Test that migrations run successfully.
     *
     * @return void
     */
    public function test_migrations_run_successfully()
    {
        // Run migrations on sqlite in-memory database
        $exitCode = Artisan::call('migrate:fresh', [
            '--database' => 'sqlite',
            '--force' => true,
        ]);

        // Assert 0 exit code
        $this->assertEquals(0, $exitCode, "Migrations failed to run. Output: \n" . Artisan::output());
        
        // Optional: Check if tables exist
        $this->assertTrue(\Schema::hasTable('users'), 'Users table missing');
        $this->assertTrue(\Schema::hasTable('locations'), 'Locations table missing');
        $this->assertTrue(\Schema::hasTable('nodes'), 'Nodes table missing');
        $this->assertTrue(\Schema::hasTable('allocations'), 'Allocations table missing');
        $this->assertTrue(\Schema::hasTable('servers'), 'Servers table missing');
    }
}
