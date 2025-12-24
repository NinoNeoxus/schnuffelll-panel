<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Egg extends Model
{
    use HasFactory;

    protected $fillable = [
        'nest_id',
        'uuid',
        'author',
        'name',
        'description',
        'features',
        'docker_images',
        'startup',
        'config_files',
        'config_startup',
        'config_logs',
        'config_stop',
        'script_install',
        'script_container',
        'script_entry',
    ];

    protected $casts = [
        'features' => 'array',
        'docker_images' => 'array',
        'config_files' => 'array',
        'config_startup' => 'array',
        'config_logs' => 'array',
    ];

    public function nest(): BelongsTo
    {
        return $this->belongsTo(Nest::class);
    }

    public function servers(): HasMany
    {
        return $this->hasMany(Server::class);
    }

    public function variables(): HasMany
    {
        return $this->hasMany(EggVariable::class);
    }

    /**
     * Get the first available Docker image.
     */
    public function getDefaultDockerImageAttribute(): string
    {
        $images = $this->docker_images;
        if (is_array($images) && count($images) > 0) {
            return reset($images);
        }
        return 'alpine:latest';
    }
}

