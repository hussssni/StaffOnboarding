<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use App\Models\StaffRecord;
use App\Models\StaffChecklistItem;

class ChecklistController extends Controller
{
    // Show the checklist for a staff member
    public function show(int $id)
    {
        Gate::authorize('admin');

        $staff = StaffRecord::with(['checklistItems.task'])->findOrFail($id);

        // Sort items by the task's sort_order for consistent display
        $items = $staff->checklistItems->sortBy('task.sort_order');

        return view('admin.staff.checklist', compact('staff', 'items'));
    }

    // Toggle a single checklist item complete/incomplete
    public function toggle(int $id, int $task_id)
    {
        Gate::authorize('admin');

        $item = StaffChecklistItem::where('staff_record_id', $id)
            ->where('task_id', $task_id)
            ->firstOrFail();

        if ($item->is_complete) {
            // Mark incomplete — clear completion data
            $item->update([
                'is_complete'  => false,
                'completed_at' => null,
                'completed_by' => null,
            ]);
        } else {
            // Mark complete — record who did it and when
            $item->update([
                'is_complete'  => true,
                'completed_at' => now(),
                'completed_by' => auth()->id(),
            ]);
        }

        return redirect()->route('admin.staff.checklist', $id)
            ->with('success', 'Checklist item updated.');
    }
}
