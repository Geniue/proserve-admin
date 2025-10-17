<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OnboardingScreen extends Model
{
    protected $fillable = [
        'title',
        'description',
        'image_url',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];
}
