<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffRecord extends Model
{
    protected $fillable = [
        'user_id',
        'full_name',
        'email',
        'department',
        'start_date',
        'status',  // Pending | Active | Offboarded
        'source',  // manual | csv_import
    ];

    // The user account linked to this staff record (nullable)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // All checklist items for this staff member
    public function checklistItems()
    {
        return $this->hasMany(StaffChecklistItem::class);
    }

    // Completed checklist items only
    public function completedItems()
    {
        return $this->hasMany(StaffChecklistItem::class)->where('is_complete', 1);
    }

    // Checklist completion percentage (0–100)
    public function progressPercent(): int
    {
        $total = $this->checklistItems()->count();
        if ($total === 0) {
            return 0;
        }
        $done = $this->completedItems()->count();
        return (int) round(($done / $total) * 100);
    }
}
