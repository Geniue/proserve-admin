<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppVersion extends Model
{
    protected $fillable = [
        'version',
        'build_number',
        'platform',
        'force_update',
        'update_message',
        'download_url',
    ];

    protected $casts = [
        'build_number' => 'integer',
        'force_update' => 'boolean',
    ];
}
