<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Egg extends Model
{
    use HasFactory;

    protected $fillable = [
        'nest_id', 'author', 'name', 'description', 
        'docker_image', 'startup_command', 'config'
    ];
    
    protected $casts = [
        'config' => 'array',
    ];

    public function nest()
    {
        return $this->belongsTo(Nest::class);
    }

    public function servers()
    {
        return $this->hasMany(Server::class);
    }
}
