<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    // Override auto-pluralization: Eloquent would guess 'audit_logs', table is 'audit_log'
    protected $table = 'audit_log';

    // audit_log is append-only — only created_at, no updated_at
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'action',        // created | updated | deleted
        'target_table',
        'target_id',
        'changed_data',  // JSON string of before/after values
        'created_at',
    ];

    // The user who performed the action
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
