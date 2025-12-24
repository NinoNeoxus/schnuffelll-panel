<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EggVariable extends Model
{
    protected $fillable = [
        'egg_id',
        'name',
        'description',
        'env_variable',
        'default_value',
        'user_viewable',
        'user_editable',
        'rules',
        'sort',
    ];

    protected $casts = [
        'user_viewable' => 'boolean',
        'user_editable' => 'boolean',
    ];

    public function egg(): BelongsTo
    {
        return $this->belongsTo(Egg::class);
    }
}
