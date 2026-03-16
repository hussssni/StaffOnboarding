<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Models\StaffRecord;
use App\Models\ChecklistTask;
use App\Models\StaffChecklistItem;
use App\Models\AuditLog;

class StaffController extends Controller
{
    // List all staff records
    public function index()
    {
        Gate::authorize('admin');

        $staffRecords = StaffRecord::with('user')
            ->orderBy('full_name')
            ->paginate(20);

        return view('admin.staff.index', compact('staffRecords'));
    }

    // Show the create form
    public function create()
    {
        Gate::authorize('admin');
        return view('admin.staff.create');
    }

    // Save a new staff record
    public function store(Request $request)
    {
        Gate::authorize('admin');

        $data = $request->validate([
            'full_name'  => ['required', 'string', 'max:255'],
            'email'      => ['required', 'email', 'max:255', 'unique:staff_records,email'],
            'department' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'status'     => ['required', 'in:Pending,Active,Offboarded'],
        ]);

        $data['source'] = 'manual';

        $staff = StaffRecord::create($data);

        // Auto-create checklist items for every defined task
        $tasks = ChecklistTask::orderBy('sort_order')->get();
        foreach ($tasks as $task) {
            StaffChecklistItem::create([
                'staff_record_id' => $staff->id,
                'task_id'         => $task->id,
                'is_complete'     => false,
            ]);
        }

        // Write to audit log
        AuditLog::create([
            'user_id'      => auth()->id(),
            'action'       => 'created',
            'target_table' => 'staff_records',
            'target_id'    => $staff->id,
            'changed_data' => json_encode($staff->toArray()),
            'created_at'   => now(),
        ]);

        return redirect()->route('admin.staff.index')
            ->with('success', "Staff record for {$staff->full_name} created.");
    }

    // Show the edit form
    public function edit(int $id)
    {
        Gate::authorize('admin');

        $staff = StaffRecord::findOrFail($id);
        return view('admin.staff.edit', compact('staff'));
    }

    // Save changes to an existing staff record
    public function update(Request $request, int $id)
    {
        Gate::authorize('admin');

        $staff = StaffRecord::findOrFail($id);

        $data = $request->validate([
            'full_name'  => ['required', 'string', 'max:255'],
            'email'      => ['required', 'email', 'max:255', "unique:staff_records,email,{$id}"],
            'department' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'status'     => ['required', 'in:Pending,Active,Offboarded'],
        ]);

        $before = $staff->toArray();
        $staff->update($data);

        // Write audit log with before/after values
        AuditLog::create([
            'user_id'      => auth()->id(),
            'action'       => 'updated',
            'target_table' => 'staff_records',
            'target_id'    => $staff->id,
            'changed_data' => json_encode(['before' => $before, 'after' => $staff->toArray()]),
            'created_at'   => now(),
        ]);

        return redirect()->route('admin.staff.index')
            ->with('success', "Staff record for {$staff->full_name} updated.");
    }

    // Delete a staff record
    public function destroy(int $id)
    {
        Gate::authorize('admin');

        $staff = StaffRecord::findOrFail($id);
        $snapshot = $staff->toArray();

        $staff->delete();

        // Write audit log with the deleted record's data
        AuditLog::create([
            'user_id'      => auth()->id(),
            'action'       => 'deleted',
            'target_table' => 'staff_records',
            'target_id'    => $id,
            'changed_data' => json_encode($snapshot),
            'created_at'   => now(),
        ]);

        return redirect()->route('admin.staff.index')
            ->with('success', 'Staff record deleted.');
    }
}
