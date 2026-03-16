<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffChecklistItem extends Model
{
    protected $fillable = [
        'staff_record_id',
        'task_id',
        'is_complete',
        'completed_at',
        'completed_by',
    ];

    protected $casts = [
        'is_complete'  => 'boolean',
        'completed_at' => 'datetime',
    ];

    // The task definition (name, description)
    public function task()
    {
        return $this->belongsTo(ChecklistTask::class);
    }

    // The staff record this item belongs to
    public function staffRecord()
    {
        return $this->belongsTo(StaffRecord::class);
    }

    // The admin user who completed this item (nullable)
    public function completedByUser()
    {
        return $this->belongsTo(User::class, 'completed_by');
    }
}
