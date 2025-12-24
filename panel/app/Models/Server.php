<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Server extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid', 'uuidShort', 'name', 'owner_id', 'node_id', 
        'egg_id', 'allocation_id', 'memory', 'swap', 'disk', 
        'io', 'cpu', 'status', 'image', 'startup'
    ];

    public function node()
    {
        return $this->belongsTo(Node::class);
    }

    public function egg()
    {
        return $this->belongsTo(Egg::class);
    }

    public function allocation()
    {
        return $this->belongsTo(Allocation::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
    
    public function allocations()
    {
        return $this->hasMany(Allocation::class);
    }
}
