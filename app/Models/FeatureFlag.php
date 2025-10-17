<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeatureFlag extends Model
{
    protected $fillable = [
        'key',
        'is_enabled',
        'description',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
    ];

    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }
}
