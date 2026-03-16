<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChecklistTask extends Model
{
    // checklist_tasks has no updated_at — set to false so Eloquent doesn't try to write it
    public $timestamps = false;

    protected $fillable = [
        'name',
        'description',
        'sort_order',
    ];

    // Staff checklist items for this task
    public function staffItems()
    {
        return $this->hasMany(StaffChecklistItem::class, 'task_id');
    }
}
