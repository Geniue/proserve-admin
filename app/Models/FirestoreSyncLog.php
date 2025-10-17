<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FirestoreSyncLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'collection',
        'document_id',
        'action',
        'direction',
        'status',
        'error_message',
        'attempted_at',
        'completed_at',
    ];

    protected $casts = [
        'attempted_at' => 'datetime',
        'completed_at' => 'datetime',
    ];
}
