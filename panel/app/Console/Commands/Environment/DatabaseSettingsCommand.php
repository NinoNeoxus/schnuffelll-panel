<?php

namespace App\Console\Commands\Environment;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class DatabaseSettingsCommand extends Command
{
    protected $signature = 'p:environment:database
        {--host= : The connection address for the MySQL server}
        {--port= : The connection port for the MySQL server}
        {--database= : The database to use}
        {--username= : Username to use when connecting}
        {--password= : Password to use for this database}';

    protected $description = 'Configure database settings for the Panel.';

    protected array $variables = [];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Configuring database settings...');
        $this->newLine();

        $this->warn('It is highly recommended to not use "localhost" as your database host.');
        $this->line('If you want to use a local connection you should be using "127.0.0.1".');
        $this->newLine();

        $this->variables['DB_HOST'] = $this->option('host') 
            ?? $this->ask('Database Host', config('database.connections.mysql.host', '127.0.0.1'));

        $this->variables['DB_PORT'] = $this->option('port') 
            ?? $this->ask('Database Port', config('database.connections.mysql.port', 3306));

        $this->variables['DB_DATABASE'] = $this->option('database') 
            ?? $this->ask('Database Name', config('database.connections.mysql.database', 'schnuffelll'));

        $this->warn('Using the "root" account for MySQL connections is not recommended.');
        $this->variables['DB_USERNAME'] = $this->option('username') 
            ?? $this->ask('Database Username', config('database.connections.mysql.username', 'schnuffelll'));

        $askForPassword = true;
        if (!empty(config('database.connections.mysql.password')) && $this->input->isInteractive()) {
            $this->variables['DB_PASSWORD'] = config('database.connections.mysql.password');
            $askForPassword = $this->confirm('Password already defined, would you like to change it?', false);
        }

        if ($askForPassword) {
            $this->variables['DB_PASSWORD'] = $this->option('password') 
                ?? $this->secret('Database Password');
        }

        // Test connection
        try {
            $this->testConnection();
            $this->info('Database connection successful!');
        } catch (\Exception $e) {
            $this->error('Unable to connect to the MySQL server: ' . $e->getMessage());
            $this->error('Your connection credentials have NOT been saved.');

            if ($this->confirm('Go back and try again?')) {
                return $this->handle();
            }

            return Command::FAILURE;
        }

        // Write to .env
        $this->writeToEnvironment();

        $this->newLine();
        $this->info('Database configuration completed successfully!');

        return Command::SUCCESS;
    }

    /**
     * Test the MySQL connection.
     */
    protected function testConnection(): void
    {
        config([
            'database.connections._schnuffelll_test' => [
                'driver' => 'mysql',
                'host' => $this->variables['DB_HOST'],
                'port' => $this->variables['DB_PORT'],
                'database' => $this->variables['DB_DATABASE'],
                'username' => $this->variables['DB_USERNAME'],
                'password' => $this->variables['DB_PASSWORD'],
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'strict' => true,
            ],
        ]);

        DB::connection('_schnuffelll_test')->getPdo();
    }

    /**
     * Write variables to .env file.
     */
    protected function writeToEnvironment(): void
    {
        $envPath = base_path('.env');
        $envContent = File::exists($envPath) ? File::get($envPath) : '';

        foreach ($this->variables as $key => $value) {
            $pattern = "/^{$key}=.*/m";
            $replacement = "{$key}={$value}";

            if (preg_match($pattern, $envContent)) {
                $envContent = preg_replace($pattern, $replacement, $envContent);
            } else {
                $envContent .= "\n{$replacement}";
            }
        }

        File::put($envPath, $envContent);
    }
}
