<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Backup extends Model
{
    protected $fillable = [
        'server_id',
        'uuid',
        'name',
        'disk',
        'size',
        'is_successful',
        'checksum',
        'completed_at',
    ];

    protected $casts = [
        'is_successful' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    /**
     * Get the full path to the backup file.
     */
    public function getPathAttribute(): string
    {
        return storage_path("app/backups/{$this->server_id}/{$this->uuid}.tar.gz");
    }
}
