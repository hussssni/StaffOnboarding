<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\StaffRecord;
use App\Models\ChecklistTask;
use App\Models\StaffChecklistItem;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ------------------------------------------------------------------
        // 1. Admin user
        // ------------------------------------------------------------------
        $admin = User::create([
            'name'     => 'Portal Admin',
            'email'    => 'admin@example.com',
            'password' => Hash::make('password'),
            'role'     => 'Admin',
        ]);

        // ------------------------------------------------------------------
        // 2. Staff user
        // ------------------------------------------------------------------
        $staffUser = User::create([
            'name'     => 'Demo Staff',
            'email'    => 'staff@example.com',
            'password' => Hash::make('password'),
            'role'     => 'Staff',
        ]);

        // ------------------------------------------------------------------
        // 3. Checklist tasks (the 5 standard onboarding steps)
        // ------------------------------------------------------------------
        $tasks = [
            ['name' => 'IT Account Setup',           'description' => 'Email, VPN, and system access configured.',          'sort_order' => 1],
            ['name' => 'Building Access Card Issued', 'description' => 'Physical access card issued for the office.',        'sort_order' => 2],
            ['name' => 'HR Forms Submitted',          'description' => 'All required HR paperwork completed and submitted.',  'sort_order' => 3],
            ['name' => 'Orientation Attended',        'description' => 'New employee orientation session completed.',         'sort_order' => 4],
            ['name' => 'Equipment Issued',            'description' => 'Laptop and any required equipment issued.',           'sort_order' => 5],
        ];

        foreach ($tasks as $taskData) {
            ChecklistTask::create($taskData);
        }

        // ------------------------------------------------------------------
        // 4. Sample staff record linked to the staff user
        // ------------------------------------------------------------------
        $staffRecord = StaffRecord::create([
            'user_id'    => $staffUser->id,
            'full_name'  => 'Demo Staff',
            'email'      => 'staff@example.com',
            'department' => 'Operations',
            'start_date' => now()->toDateString(),
            'status'     => 'Pending',
            'source'     => 'manual',
        ]);

        // Create a checklist item for each task for this staff member
        foreach (ChecklistTask::all() as $task) {
            StaffChecklistItem::create([
                'staff_record_id' => $staffRecord->id,
                'task_id'         => $task->id,
                'is_complete'     => false,
            ]);
        }
    }
}
