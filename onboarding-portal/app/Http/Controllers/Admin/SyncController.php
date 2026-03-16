<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Models\StaffRecord;
use App\Models\ChecklistTask;
use App\Models\StaffChecklistItem;
use App\Models\SyncLog;
use App\Models\AuditLog;

class SyncController extends Controller
{
    // Show the CSV upload form
    public function show()
    {
        Gate::authorize('admin');
        return view('admin.sync');
    }

    // Process the uploaded CSV file
    public function run(Request $request)
    {
        Gate::authorize('admin');

        // Validate that a CSV file was uploaded
        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);

        $file     = $request->file('csv_file');
        $filename = $file->getClientOriginalName();
        $path     = $file->getRealPath();

        $counts = ['inserted' => 0, 'updated' => 0, 'skipped' => 0];

        // Open and parse the CSV file
        $handle = fopen($path, 'r');

        // Read and discard the header row
        $headers = fgetcsv($handle);

        // Process each data row
        while (($row = fgetcsv($handle)) !== false) {
            // Map columns to named keys based on header order
            $data = array_combine($headers, $row);

            // Skip rows missing required fields
            if (empty($data['email'])) {
                continue;
            }

            $email = trim($data['email']);

            // Look up an existing record by email
            $existing = StaffRecord::where('email', $email)->first();

            if ($existing === null) {
                // INSERT — new staff member
                $staff = StaffRecord::create([
                    'full_name'  => trim($data['full_name']  ?? ''),
                    'email'      => $email,
                    'department' => trim($data['department'] ?? ''),
                    'start_date' => trim($data['start_date'] ?? ''),
                    'status'     => trim($data['status']     ?? 'Pending'),
                    'source'     => 'csv_import',
                ]);

                // Auto-create checklist items for this new staff member
                foreach (ChecklistTask::all() as $task) {
                    StaffChecklistItem::create([
                        'staff_record_id' => $staff->id,
                        'task_id'         => $task->id,
                        'is_complete'     => false,
                    ]);
                }

                $this->logSync($filename, 'inserted', $email, null);
                $this->logAudit('created', $staff->id, $staff->toArray());
                $counts['inserted']++;

            } else {
                // Check if any field actually differs
                $changed = $existing->full_name  !== trim($data['full_name']  ?? '')
                        || $existing->department !== trim($data['department'] ?? '')
                        || $existing->start_date !== trim($data['start_date'] ?? '')
                        || $existing->status     !== trim($data['status']     ?? 'Pending');

                if ($changed) {
                    // UPDATE — data has changed
                    $before = $existing->toArray();

                    $existing->update([
                        'full_name'  => trim($data['full_name']  ?? ''),
                        'department' => trim($data['department'] ?? ''),
                        'start_date' => trim($data['start_date'] ?? ''),
                        'status'     => trim($data['status']     ?? 'Pending'),
                        'source'     => 'csv_import',
                    ]);

                    $this->logSync($filename, 'updated', $email, null);
                    $this->logAudit('updated', $existing->id, ['before' => $before, 'after' => $existing->toArray()]);
                    $counts['updated']++;

                } else {
                    // SKIP — record already exists and is identical
                    $this->logSync($filename, 'skipped', $email, 'No changes detected');
                    $counts['skipped']++;
                }
            }
        }

        fclose($handle);

        return redirect()->route('admin.sync')
            ->with('sync_results', $counts)
            ->with('sync_filename', $filename);
    }

    // Write a row-level entry to sync_log
    private function logSync(string $filename, string $action, string $identifier, ?string $notes): void
    {
        SyncLog::create([
            'filename'          => $filename,
            'action'            => $action,
            'record_identifier' => $identifier,
            'notes'             => $notes,
            'synced_by'         => auth()->id(),
            'created_at'        => now(),
        ]);
    }

    // Write an entry to audit_log for the sync action
    private function logAudit(string $action, int $targetId, array $data): void
    {
        AuditLog::create([
            'user_id'      => auth()->id(),
            'action'       => $action,
            'target_table' => 'staff_records',
            'target_id'    => $targetId,
            'changed_data' => json_encode($data),
            'created_at'   => now(),
        ]);
    }
}
