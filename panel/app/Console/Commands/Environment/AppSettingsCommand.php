<?php

namespace App\Console\Commands\Environment;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class AppSettingsCommand extends Command
{
    /**
     * Cache driver options.
     */
    public const CACHE_DRIVERS = [
        'redis' => 'Redis (recommended)',
        'file' => 'Filesystem',
        'database' => 'MySQL Database',
    ];

    /**
     * Session driver options.
     */
    public const SESSION_DRIVERS = [
        'redis' => 'Redis (recommended)',
        'file' => 'Filesystem',
        'database' => 'MySQL Database',
        'cookie' => 'Cookie',
    ];

    /**
     * Queue driver options.
     */
    public const QUEUE_DRIVERS = [
        'redis' => 'Redis (recommended)',
        'database' => 'MySQL Database',
        'sync' => 'Sync (not recommended)',
    ];

    protected $signature = 'p:environment:setup
        {--author= : The email that services created on this instance should be linked to}
        {--url= : The URL that this Panel is running on}
        {--timezone= : The timezone to use for Panel times}
        {--cache= : The cache driver backend to use}
        {--session= : The session driver backend to use}
        {--queue= : The queue driver backend to use}
        {--redis-host= : Redis host to use for connections}
        {--redis-pass= : Password used to connect to redis}
        {--redis-port= : Port to connect to redis over}
        {--settings-ui= : Enable or disable the settings UI}';

    protected $description = 'Configure basic environment settings for the Panel.';

    protected array $variables = [];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Configuring Schnuffelll Panel environment...');
        $this->newLine();

        // Author email
        $this->comment('Provide the email address that will be used for system emails and Let\'s Encrypt.');
        $this->variables['APP_SERVICE_AUTHOR'] = $this->option('author') 
            ?? $this->ask('Author Email', config('app.service_author', 'admin@example.com'));

        if (!filter_var($this->variables['APP_SERVICE_AUTHOR'], FILTER_VALIDATE_EMAIL)) {
            $this->error('The email address provided is invalid.');
            return Command::FAILURE;
        }

        // Application URL
        $this->comment('The application URL MUST begin with https:// or http://');
        $this->variables['APP_URL'] = $this->option('url') 
            ?? $this->ask('Application URL', config('app.url', 'http://localhost'));

        // Timezone
        $this->comment('Timezone should match one of PHP\'s supported timezones.');
        $this->variables['APP_TIMEZONE'] = $this->option('timezone') 
            ?? $this->anticipate('Application Timezone', \DateTimeZone::listIdentifiers(), config('app.timezone', 'UTC'));

        // Cache driver
        $selected = config('cache.default', 'file');
        $this->variables['CACHE_DRIVER'] = $this->option('cache') 
            ?? $this->choice('Cache Driver', self::CACHE_DRIVERS, $selected);

        // Session driver
        $selected = config('session.driver', 'file');
        $this->variables['SESSION_DRIVER'] = $this->option('session') 
            ?? $this->choice('Session Driver', self::SESSION_DRIVERS, $selected);

        // Queue driver
        $selected = config('queue.default', 'sync');
        $this->variables['QUEUE_CONNECTION'] = $this->option('queue') 
            ?? $this->choice('Queue Driver', self::QUEUE_DRIVERS, $selected);

        // Check if any driver uses Redis
        $usesRedis = in_array('redis', [
            $this->variables['CACHE_DRIVER'],
            $this->variables['SESSION_DRIVER'],
            $this->variables['QUEUE_CONNECTION'],
        ]);

        if ($usesRedis) {
            $this->configureRedis();
        }

        // Settings UI
        if (!is_null($this->option('settings-ui'))) {
            $this->variables['APP_ENVIRONMENT_ONLY'] = $this->option('settings-ui') === 'true' ? 'false' : 'true';
        } else {
            $enabled = $this->confirm('Enable UI based settings editor?', true);
            $this->variables['APP_ENVIRONMENT_ONLY'] = $enabled ? 'false' : 'true';
        }

        // Make cookies secure if using HTTPS
        if (str_starts_with($this->variables['APP_URL'], 'https://')) {
            $this->variables['SESSION_SECURE_COOKIE'] = 'true';
        }

        // Write to .env
        $this->writeToEnvironment();

        $this->newLine();
        $this->info('Environment configuration completed successfully!');

        return Command::SUCCESS;
    }

    /**
     * Configure Redis settings.
     */
    protected function configureRedis(): void
    {
        $this->newLine();
        $this->comment('You\'ve selected Redis for one or more services. Provide connection information below.');

        $this->variables['REDIS_HOST'] = $this->option('redis-host') 
            ?? $this->ask('Redis Host', config('database.redis.default.host', '127.0.0.1'));

        $this->variables['REDIS_PASSWORD'] = $this->option('redis-pass') 
            ?? $this->secret('Redis Password (leave empty if none)');

        if (empty($this->variables['REDIS_PASSWORD'])) {
            $this->variables['REDIS_PASSWORD'] = 'null';
        }

        $this->variables['REDIS_PORT'] = $this->option('redis-port') 
            ?? $this->ask('Redis Port', config('database.redis.default.port', 6379));
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
