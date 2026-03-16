<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SyncLog extends Model
{
    // Override auto-pluralization: Eloquent would guess 'sync_logs', table is 'sync_log'
    protected $table = 'sync_log';

    // sync_log only has created_at, no updated_at
    public $timestamps = false;

    protected $fillable = [
        'filename',
        'action',           // inserted | updated | skipped
        'record_identifier', // email or ID from CSV row
        'notes',
        'synced_by',        // FK → users.id
        'created_at',
    ];

    public function syncedByUser()
    {
        return $this->belongsTo(User::class, 'synced_by');
    }
}
