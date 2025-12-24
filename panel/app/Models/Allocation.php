<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Allocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'node_id', 'ip', 'alias', 'port', 'server_id', 'notes'
    ];

    public function node()
    {
        return $this->belongsTo(Node::class);
    }

    public function server()
    {
        return $this->belongsTo(Server::class);
    }
}
