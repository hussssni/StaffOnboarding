<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use App\Models\StaffRecord;
use App\Models\StaffChecklistItem;

class StaffDashboardController extends Controller
{
    // Staff dashboard — own record summary + checklist progress
    public function index()
    {
        $record = StaffRecord::where('user_id', auth()->id())->firstOrFail();

        $items = StaffChecklistItem::with('task')
            ->where('staff_record_id', $record->id)
            ->get()
            ->sortBy('task.sort_order');

        $progress = $record->progressPercent();

        return view('staff.dashboard', compact('record', 'items', 'progress'));
    }

    // Staff read-only view of their own record
    public function record()
    {
        $record = StaffRecord::where('user_id', auth()->id())->firstOrFail();

        // Confirm the logged-in user is allowed to see this record
        Gate::authorize('view-own-record', $record);

        return view('staff.record', compact('record'));
    }

    // Staff read-only view of their own checklist
    public function checklist()
    {
        $record = StaffRecord::where('user_id', auth()->id())->firstOrFail();

        Gate::authorize('view-own-record', $record);

        $items = StaffChecklistItem::with('task')
            ->where('staff_record_id', $record->id)
            ->get()
            ->sortBy('task.sort_order');

        $progress = $record->progressPercent();

        return view('staff.checklist', compact('record', 'items', 'progress'));
    }
}
