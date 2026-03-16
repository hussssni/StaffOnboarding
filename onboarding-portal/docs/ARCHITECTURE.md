# Architecture Notes — Munk Onboarding Portal

## Pattern: MVC via Laravel

| Layer | Location | Responsibility |
|---|---|---|
| Models | `app/Models/` | Eloquent ORM — maps to DB tables, defines relationships |
| Views | `resources/views/` | Blade templates — HTML only, no business logic |
| Controllers | `app/Http/Controllers/` | Handle HTTP, validate input, call models, pass to views |

## Access Control: Laravel Gates

Gates are defined in `app/Providers/AuthServiceProvider.php`. Two gates:

- `admin` — checks `$user->role === 'Admin'`
- `view-own-record` — Admin or the staff member whose record it is

Every admin controller method calls `Gate::authorize('admin')` on line 1. This makes access checks **visible at the point of use** — no need to trace through middleware config.

Blade templates use `@can('admin')` to conditionally render admin-only nav links.

**Why Gates over middleware roles?** Easier to audit, easier to demonstrate in an interview, easier to extend.

## Database: SQL Server

Using the `sqlsrv` PDO driver. SQL Server-specific syntax used throughout:
- `INT IDENTITY PRIMARY KEY` instead of MySQL `AUTO_INCREMENT`
- `NVARCHAR` instead of `VARCHAR` (Unicode-safe)
- `DATETIME2` instead of `TIMESTAMP`
- `BIT` for booleans (0/1) instead of `TINYINT` or `BOOLEAN`
- `TOP N` for limit queries where used in raw SQL

All foreign keys are explicit in migrations. Migration run order matters:
1. `users`
2. `staff_records` (needs `users.id`)
3. `checklist_tasks` (standalone)
4. `staff_checklist_items` (needs `staff_records.id`, `checklist_tasks.id`, `users.id`)
5. `sync_log` (needs `users.id`)
6. `audit_log` (needs `users.id`)

## Audit Trail

Every create/update/delete on `staff_records` writes to `audit_log`. Done manually inside `StaffController` (not via Eloquent observers) so the logic is readable without knowing Laravel's event system.

The `audit_log` table is **append-only** — no update or delete routes exist anywhere in the codebase.

## CSV Sync Utility

`SyncController@run` implements a simple upsert loop:
- Match rows by email
- INSERT if not found → log `inserted`
- UPDATE if found and data differs → log `updated`
- Skip if identical → log `skipped`

Results logged to both `sync_log` (per-row detail) and `audit_log` (admin action record).

## Folder Structure

```
app/
  Http/Controllers/
    Auth/LoginController.php          — handles /login POST
    Admin/DashboardController.php     — admin home
    Admin/StaffController.php         — CRUD for staff_records
    Admin/ChecklistController.php     — toggle checklist items
    Admin/SyncController.php          — CSV import/upsert
    Admin/AuditLogController.php      — view audit_log
    Staff/StaffDashboardController.php — staff-facing views
  Models/
    User.php
    StaffRecord.php
    ChecklistTask.php
    StaffChecklistItem.php
    SyncLog.php
    AuditLog.php
  Providers/
    AuthServiceProvider.php           — Gate definitions

database/
  migrations/                         — one file per table, ordered
  seeders/DatabaseSeeder.php          — admin + staff user, tasks, sample record

resources/views/
  layouts/app.blade.php               — shared nav + Bootstrap 5 shell
  auth/login.blade.php
  admin/dashboard.blade.php
  admin/staff/index.blade.php
  admin/staff/create.blade.php
  admin/staff/edit.blade.php
  admin/staff/checklist.blade.php
  admin/sync.blade.php
  admin/audit-log.blade.php
  staff/dashboard.blade.php
  staff/record.blade.php
  staff/checklist.blade.php

routes/web.php                        — all URL definitions
docs/vhost.conf                       — Apache virtual host
docs/ARCHITECTURE.md                  — this file
storage/app/imports/sample_import.csv — demo CSV for sync tool
```
