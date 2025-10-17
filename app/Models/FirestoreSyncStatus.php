<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FirestoreSyncStatus extends Model
{
    protected $table = 'firestore_sync_status';

    protected $fillable = [
        'collection',
        'last_sync_at',
        'last_document_timestamp',
        'total_documents',
        'status',
    ];

    protected $casts = [
        'last_sync_at' => 'datetime',
        'last_document_timestamp' => 'datetime',
        'total_documents' => 'integer',
    ];
}
