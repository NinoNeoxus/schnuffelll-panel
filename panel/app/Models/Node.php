<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Node extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'location_id', 'public', 'fqdn', 'scheme',
        'behind_proxy', 'memory', 'memory_overallocate',
        'disk', 'disk_overallocate', 'daemon_token_id',
        'daemon_token', 'daemon_listen', 'daemon_sftp',
        'maintenance_mode'
    ];
    
    // Hide sensitive token by default
    protected $hidden = [
        'daemon_token', 'daemon_token_id'
    ];

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
