<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Nest extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'name', 'description', 'author'
    ];

    public function eggs()
    {
        return $this->hasMany(Egg::class);
    }
}
