<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Symfony\Component\Yaml\Yaml;

class Node extends Model
{
    use HasFactory;

    /**
     * The default location of server files.
     */
    public const DEFAULT_DAEMON_BASE = '/var/lib/pterodactyl/volumes';

    public const DAEMON_TOKEN_ID_LENGTH = 16;
    public const DAEMON_TOKEN_LENGTH = 64;

    protected $fillable = [
        'uuid',
        'name',
        'description',
        'location_id',
        'public',
        'fqdn',
        'scheme',
        'behind_proxy',
        'memory',
        'memory_overallocate',
        'disk',
        'disk_overallocate',
        'daemon_token_id',
        'daemon_token',
        'daemon_listen',
        'daemon_sftp',
        'daemon_base',
        'upload_size',
        'maintenance_mode',
    ];

    protected $hidden = [
        'daemon_token',
        'daemon_token_id',
    ];

    protected $casts = [
        'location_id' => 'integer',
        'memory' => 'integer',
        'disk' => 'integer',
        'daemon_listen' => 'integer',
        'daemon_sftp' => 'integer',
        'behind_proxy' => 'boolean',
        'public' => 'boolean',
        'maintenance_mode' => 'boolean',
    ];

    protected $attributes = [
        'daemon_listen' => 8080,
        'daemon_sftp' => 2022,
        'public' => true,
        'behind_proxy' => false,
        'memory_overallocate' => 0,
        'disk_overallocate' => 0,
        'daemon_base' => self::DEFAULT_DAEMON_BASE,
        'maintenance_mode' => false,
    ];

    /**
     * Get the connection address for this node.
     */
    public function getConnectionAddress(): string
    {
        return sprintf('%s://%s:%s', $this->scheme, $this->fqdn, $this->daemon_listen);
    }

    /**
     * Get the configuration as an array for Wings.
     */
    public function getConfiguration(): array
    {
        return [
            'debug' => false,
            'uuid' => $this->uuid,
            'token_id' => $this->daemon_token_id,
            'token' => $this->daemon_token,
            'api' => [
                'host' => '0.0.0.0',
                'port' => $this->daemon_listen,
                'ssl' => [
                    'enabled' => (!$this->behind_proxy && $this->scheme === 'https'),
                    'cert' => '/etc/letsencrypt/live/' . Str::lower($this->fqdn) . '/fullchain.pem',
                    'key' => '/etc/letsencrypt/live/' . Str::lower($this->fqdn) . '/privkey.pem',
                ],
                'upload_limit' => $this->upload_size ?? 100,
            ],
            'system' => [
                'data' => $this->daemon_base ?? self::DEFAULT_DAEMON_BASE,
                'sftp' => [
                    'bind_port' => $this->daemon_sftp,
                ],
            ],
            'allowed_mounts' => [],
            'remote' => config('app.url'),
        ];
    }

    /**
     * Get the configuration in YAML format.
     */
    public function getYamlConfiguration(): string
    {
        return Yaml::dump($this->getConfiguration(), 4, 2, Yaml::DUMP_EMPTY_ARRAY_AS_SEQUENCE);
    }

    /**
     * Get the configuration in JSON format.
     */
    public function getJsonConfiguration(bool $pretty = false): string
    {
        return json_encode(
            $this->getConfiguration(),
            $pretty ? JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT : JSON_UNESCAPED_SLASHES
        );
    }

    /**
     * Check if node is under maintenance.
     */
    public function isUnderMaintenance(): bool
    {
        return $this->maintenance_mode;
    }

    /**
     * Check if node is viable for new server.
     */
    public function isViable(int $memory = 0, int $disk = 0): bool
    {
        $usedMemory = $this->servers()->sum('memory');
        $usedDisk = $this->servers()->sum('disk');

        $memoryLimit = $this->memory * (1.0 + ($this->memory_overallocate / 100.0));
        $diskLimit = $this->disk * (1.0 + ($this->disk_overallocate / 100.0));

        return ($usedMemory + $memory) <= $memoryLimit && ($usedDisk + $disk) <= $diskLimit;
    }

    /**
     * Generate tokens for a new node.
     */
    public static function generateTokens(): array
    {
        return [
            'daemon_token_id' => Str::random(self::DAEMON_TOKEN_ID_LENGTH),
            'daemon_token' => Str::random(self::DAEMON_TOKEN_LENGTH),
        ];
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function servers()
    {
        return $this->hasMany(Server::class);
    }

    public function allocations()
    {
        return $this->hasMany(Allocation::class);
    }
}
