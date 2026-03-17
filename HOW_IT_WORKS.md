# How the Staff Onboarding Portal Works

**A Plain-English + Simple-Technical Guide**
*Written so anyone — from a hiring manager to a junior dev — can understand exactly what we built and why.*

---

## Table of Contents

1. [What Is This App?](#1-what-is-this-app)
2. [The Tech Stack](#2-the-tech-stack)
3. [How Logging In Works](#3-how-logging-in-works)
4. [The Two User Types (Roles)](#4-the-two-user-types-roles)
5. [Staff Records](#5-staff-records)
6. [The Onboarding Checklist](#6-the-onboarding-checklist)
7. [The CSV Import Tool](#7-the-csv-import-tool)
8. [The Audit Trail](#8-the-audit-trail)
9. [The Dashboard](#9-the-dashboard)
10. [The Database Tables](#10-the-database-tables)
11. [How Pages Are Routed](#11-how-pages-are-routed)
12. [How the Folder Is Organized](#12-how-the-folder-is-organized)
13. [The Web Server Config (Apache)](#13-the-web-server-config-apache)
14. [How to Get It Running Locally](#14-how-to-get-it-running-locally)

---

## 1. What Is This App?

### Plain English

Imagine a new employee starts at the school. HR needs to track them — have they got their ID card? Did they set up their email? Did they attend orientation? Right now, that might all live in a spreadsheet, a shared drive, or someone's memory.

This app replaces that mess. It's a private website — visible only inside the organization — where:
- **HR/Admin staff** can add new hires, see everyone's progress, and tick off checklist items.
- **New employees** can log in and see only their own profile and checklist.

Nobody outside the organization can see or access it.

### Simply Technical

This is a **CRUD web application** (Create, Read, Update, Delete) running on a private server. It uses a **relational database** to store structured records about staff members and their onboarding progress. There is no public API — all interaction happens through server-rendered HTML pages. Authentication is **session-based**: the server stores your login state in a cookie, not a token.

---

## 2. The Tech Stack

### Plain English

Think of "the stack" as the set of tools used to build the app — like choosing what brand of oven, fridge, and countertop you'll use in a kitchen. Each tool does one specific job.

| What it does | Tool used |
|---|---|
| The programming language that runs the app | PHP |
| The framework that organizes the PHP code | Laravel |
| The database that stores all the data | Microsoft SQL Server |
| The web server that delivers pages to browsers | Apache |
| The templates that define what pages look like | Blade (Laravel's HTML system) |
| The CSS library that makes pages look decent | Bootstrap 5 |

### Simply Technical

- **PHP 8 + Laravel 11** — PHP is the server-side language. Laravel is a framework on top of PHP that gives you routing, database access, authentication, and more, out of the box. Think of Laravel as the scaffolding that lets you write less boilerplate.
- **Microsoft SQL Server** — A relational database (tables, rows, columns, foreign keys). Accessed from PHP via the `sqlsrv` PDO driver — a PHP extension that speaks the SQL Server protocol.
- **Apache HTTP Server** — Receives HTTP requests from a browser and hands them to PHP. The `mod_rewrite` module is required so that all URLs (e.g. `/admin/staff`) route through Laravel's single entry point (`public/index.php`).
- **Blade templates** — Laravel's server-side HTML templating engine. Variables from the controller are injected into `.blade.php` files to produce the final HTML sent to the browser.
- **Bootstrap 5** — A CSS utility library loaded via CDN. Provides responsive grids, buttons, badges, and tables without writing custom CSS.

---

## 3. How Logging In Works

### Plain English

When you go to the site, you land on a login page. You type your email and password. If they match what's in the database, the site remembers you and lets you in. If you try to visit any page without logging in first, you get sent back to the login screen automatically.

There's no "Sign Up" button. Only an Admin can create new accounts for people.

### Simply Technical

- The login form `POST`s to `/login`. Laravel's built-in `Auth::attempt()` checks the email against the `users` table and compares the submitted password against the **bcrypt hash** stored in the database. Plain-text passwords are never stored.
- On success, Laravel writes the user's `id` to the **session** (a server-side store tied to a cookie in the browser). All subsequent requests carry that cookie, so the server knows who you are.
- The `auth` middleware is applied to all protected routes. If no valid session exists, it redirects to `/login` automatically.
- Account creation is Admin-only — there is no public registration route. The `DatabaseSeeder` creates the initial admin and staff accounts when the app is first set up.

---

## 4. The Two User Types (Roles)

### Plain English

There are two kinds of users:

**Admin** — full control. Can see everyone, edit records, run imports, and view the activity log.

**Staff** — limited view. Can only see their own profile and their own checklist. Can't see anyone else's information.

The app knows which type you are based on a field in your user account. If you're a Staff user and you try to visit an Admin page by typing the URL directly, the app blocks you.

### Simply Technical

Roles are stored in a `role` column on the `users` table — either `'Admin'` or `'Staff'`.

Access control is enforced via **Laravel Gates**, defined in `AuthServiceProvider.php`:

```php
// A Gate is just a named function that returns true or false
Gate::define('admin', function (User $user) {
    return $user->role === 'Admin';
});
```

In every admin controller method, the first line is:
```php
Gate::authorize('admin'); // Throws a 403 if the user isn't Admin
```

In Blade templates, `@can('admin') ... @endcan` conditionally renders admin navigation links. This approach was chosen over middleware-based roles because the logic is **explicit and auditable** — you can see the access check inline rather than tracing it through a middleware chain.

---

## 5. Staff Records

### Plain English

A "staff record" is the main profile for each person going through onboarding. It includes their name, email, department, when they started, and what status they're in: Are they still waiting to start? Are they active? Have they left?

Admins can create these records, edit them, and delete them. Staff members can only read their own.

### Simply Technical

Data lives in the `staff_records` table. The `StaffRecord` Eloquent model maps to this table, with relationships to `User` (via `user_id`), `StaffChecklistItem`, and `AuditLog`.

The `StaffController` handles all CRUD operations:

| HTTP Method | Route | Action |
|---|---|---|
| GET | `/admin/staff` | `index` — paginated list of all staff |
| GET | `/admin/staff/create` | `create` — show empty form |
| POST | `/admin/staff` | `store` — validate + insert row |
| GET | `/admin/staff/{id}/edit` | `edit` — show filled form |
| PUT | `/admin/staff/{id}` | `update` — validate + update row |
| DELETE | `/admin/staff/{id}` | `destroy` — soft delete or hard delete row |

Every mutating action (`store`, `update`, `destroy`) writes a row to `audit_log` immediately after.

Status field values are constrained to: `'Pending'`, `'Active'`, `'Offboarded'`. Source field values: `'manual'` (created via the form) or `'csv_import'` (created via the sync tool).

---

## 6. The Onboarding Checklist

### Plain English

Every new staff member has a personal to-do list of things that need to happen before they're fully onboarded:

1. IT Account Setup
2. Building Access Card Issued
3. HR Forms Submitted
4. Orientation Attended
5. Equipment Issued

The Admin can tick items on or off for any staff member. The staff member can see their own checklist but can't change it themselves. A progress bar shows how far along they are (e.g. 3 out of 5 = 60%).

### Simply Technical

The checklist uses two tables:
- `checklist_tasks` — the master list of task names (seeded once, shared across all staff).
- `staff_checklist_items` — one row per staff member per task, with `is_complete` (BIT), `completed_at` (timestamp), and `completed_by` (FK to the admin who checked it).

When a new staff record is created, all 5 tasks are automatically inserted into `staff_checklist_items` for that person with `is_complete = 0`.

The `ChecklistController@toggle` action flips `is_complete` between 0 and 1, sets `completed_at = now()` or nulls it out, and records `completed_by = auth()->id()`.

Progress percentage in the view:
```php
$progress = ($completedCount / $totalCount) * 100;
```
Rendered as a Bootstrap progress bar: `<div class="progress-bar" style="width: {{ $progress }}%">`.

---

## 7. The CSV Import Tool

### Plain English

Before this portal existed, staff data was probably kept in a spreadsheet or some old system. This tool lets an Admin upload a `.csv` file (a simple spreadsheet format), and the app will automatically read each row and either add the person if they're new, or update their info if they already exist.

After the import runs, the Admin sees a summary: "5 inserted, 2 updated, 1 skipped." Every row's result is saved so there's a record of what happened.

This is the most important feature — it directly mirrors a common real-world need to migrate legacy staff data.

### Simply Technical

`SyncController@run` handles the `POST /admin/sync` route:

1. **Validate** — confirm the uploaded file has a `.csv` extension and is under the max size.
2. **Parse** — use PHP's `fgetcsv()` in a loop to read each row into an associative array.
3. **Upsert logic** for each row:
   - Query `staff_records` for a row matching that email.
   - **Not found** → `StaffRecord::create([...])` + log `'inserted'`
   - **Found, data differs** → `$record->update([...])` + log `'updated'`
   - **Found, data identical** → no write + log `'skipped'`
4. **Log each row** to `sync_log` (source file name, action, record identifier, notes, who ran it).
5. **Also write to `audit_log`** for inserted/updated rows (for the full audit trail).
6. **Redirect** to a results page passing the counts as session flash data.

The CSV is validated against expected columns (`full_name`, `email`, `department`, `start_date`, `status`) before processing begins. Malformed rows are skipped with a note logged.

---

## 8. The Audit Trail

### Plain English

Whenever anyone creates, changes, or deletes a staff record, the app saves a note in a separate "audit log" table — like a record of everything that happened and who did it. You can think of it like a paper trail.

"At 2pm on March 16th, the admin user updated Jane Smith's status from Pending to Active."

Only Admins can see this log. Nobody can edit or delete entries from it — it's permanent.

### Simply Technical

The `audit_log` table stores: `user_id`, `action` (`'created'`/`'updated'`/`'deleted'`), `target_table`, `target_id`, and `changed_data` (JSON blob of the before/after values).

Logging is done manually inside `StaffController` — not via Eloquent observers — so the logic is explicit:

```php
// After $staff->save() in StaffController@store:
AuditLog::create([
    'user_id'      => auth()->id(),
    'action'       => 'created',
    'target_table' => 'staff_records',
    'target_id'    => $staff->id,
    'changed_data' => json_encode($staff->toArray()),
]);

// For updates, store old and new:
'changed_data' => json_encode(['before' => $old, 'after' => $staff->toArray()]),
```

The `audit_log` table has **no update or delete routes** — it is append-only. The `AuditLog` model has `$guarded = []` to allow mass-assignment but no `->update()` or `->delete()` calls anywhere in the codebase.

---

## 9. The Dashboard

### Plain English

After logging in, you land on a dashboard. What you see depends on who you are:

**Admin dashboard:** Shows the total number of staff, a breakdown (how many are Pending, Active, or Offboarded), and a recent list of changes from the audit log — like a news feed of activity.

**Staff dashboard:** Shows just your own info — your name, department, start date, current status, and your personal checklist progress bar.

Both use the same page layout (header, nav, footer). Only the content in the middle changes.

### Simply Technical

The `GET /dashboard` route checks the user's role and redirects:
```php
return auth()->user()->role === 'Admin'
    ? redirect('/admin/dashboard')
    : redirect('/staff/dashboard');
```

**Admin dashboard** (`DashboardController@index`) runs aggregation queries:
```php
$counts = StaffRecord::selectRaw("status, COUNT(*) as count")
    ->groupBy('status')
    ->get();
$recentAudit = AuditLog::with('user')->latest()->take(10)->get();
```

**Staff dashboard** (`StaffDashboardController@index`) queries only the logged-in user's record:
```php
$record = StaffRecord::where('user_id', auth()->id())->firstOrFail();
$checklistItems = StaffChecklistItem::with('task')
    ->where('staff_record_id', $record->id)
    ->get();
```

Both views extend the same `layouts/app.blade.php` shell. Role-specific nav items are wrapped in `@can('admin')` directives.

---

## 10. The Database Tables

### Plain English

The database is like a set of organized spreadsheets, each holding one type of information. They're linked together — for example, a checklist item knows which staff member it belongs to by storing that person's ID number.

Here's what each "spreadsheet" (table) stores:

| Table | What It's For |
|---|---|
| `users` | Login accounts — email, password, role |
| `staff_records` | The onboarding profile for each person |
| `checklist_tasks` | The master list of 5 onboarding tasks |
| `staff_checklist_items` | Which tasks each person has completed |
| `sync_log` | A record of every CSV import row and what happened |
| `audit_log` | A permanent record of every change made to staff records |

### Simply Technical

All tables use SQL Server syntax (`INT IDENTITY PRIMARY KEY`, `NVARCHAR`, `DATETIME2`, `BIT`).

**Relationships:**
- `staff_records.user_id → users.id` (nullable — a record can exist without a portal login)
- `staff_checklist_items.staff_record_id → staff_records.id`
- `staff_checklist_items.task_id → checklist_tasks.id`
- `staff_checklist_items.completed_by → users.id` (nullable)
- `sync_log.synced_by → users.id`
- `audit_log.user_id → users.id`

**Eloquent relationships** (defined in models):
```php
// StaffRecord.php
public function user(): BelongsTo { return $this->belongsTo(User::class); }
public function checklistItems(): HasMany { return $this->hasMany(StaffChecklistItem::class); }

// StaffChecklistItem.php
public function task(): BelongsTo { return $this->belongsTo(ChecklistTask::class); }
```

Migrations are run **in dependency order**: `users` first, then `staff_records` (which needs `users.id`), then `staff_checklist_items` (which needs both `staff_records.id` and `checklist_tasks.id`).

---

## 11. How Pages Are Routed

### Plain English

A "route" is a rule that says: "When someone visits this URL, run this piece of code and show them this page."

The app's routes are split into two groups:
- `/admin/...` — only Admins can access these.
- `/staff/...` — any logged-in user can access these (but they'll only see their own data).

If you're not logged in and you try any of these, you get sent to the login page.

### Simply Technical

All routes are defined in `routes/web.php`. Protected routes use two layers:

1. **`auth` middleware** — Laravel built-in. Redirects to `/login` if no session.
2. **`Gate::authorize('admin')`** — called inside each admin controller method. Throws `403 Forbidden` if the user's role isn't `'Admin'`.

Route groups:
```php
// All /admin routes require auth middleware
Route::middleware('auth')->prefix('admin')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::resource('/staff', StaffController::class);
    Route::get('/staff/{id}/checklist', [ChecklistController::class, 'show']);
    Route::post('/staff/{id}/checklist/{task_id}', [ChecklistController::class, 'toggle']);
    Route::get('/sync', [SyncController::class, 'show']);
    Route::post('/sync', [SyncController::class, 'run']);
    Route::get('/audit-log', [AuditLogController::class, 'index']);
});

Route::middleware('auth')->prefix('staff')->group(function () {
    Route::get('/dashboard', [StaffDashboardController::class, 'index']);
    Route::get('/record', [StaffDashboardController::class, 'record']);
    Route::get('/checklist', [StaffDashboardController::class, 'checklist']);
});
```

The `Gate::authorize('admin')` check inside each admin controller method is intentional — it keeps access control **visible at the point of use** rather than buried in middleware configuration.

---

## 12. How the Folder Is Organized

### Plain English

The project follows a standard layout where each type of file has its own folder. You don't mix your HTML templates with your database logic. Each piece has a clear home.

```
onboarding-portal/
├── app/               ← All the PHP logic lives here
│   ├── Http/
│   │   └── Controllers/   ← One file per feature (login, staff, checklist, etc.)
│   ├── Models/            ← One file per database table
│   └── Providers/         ← Where the role (Gate) rules are defined
├── database/
│   ├── migrations/        ← Instructions for creating the database tables
│   └── seeders/           ← Starter data (admin account, checklist tasks, etc.)
├── resources/views/       ← HTML templates (the pages users see)
├── routes/web.php         ← The URL map for the whole app
├── docs/                  ← Extra documentation and server config files
└── README.md              ← How to set up and run the project
```

### Simply Technical

Laravel uses **MVC architecture** (Model-View-Controller):

- **Models** (`app/Models/`) — Eloquent classes that map to database tables. They define relationships, validation rules, and mass-assignable fields (`$fillable`).
- **Views** (`resources/views/`) — Blade `.blade.php` template files. They receive data from controllers via `view('name', ['key' => $value])` and render HTML. No business logic lives here.
- **Controllers** (`app/Http/Controllers/`) — Handle HTTP requests. Validate input, call models, pass data to views. Each controller is namespaced under `Admin\` or `Staff\` to mirror the URL structure.

The `AuthServiceProvider` (`app/Providers/`) is the single place where all Gate definitions live — this is a conscious architectural choice for audibility.

---

## 13. The Web Server Config (Apache)

### Plain English

Apache is the software that sits between the internet and your app. When a browser asks for a page, Apache receives the request and hands it to PHP to process. The configuration file tells Apache where to find the app and how to handle requests.

The most important setting is `AllowOverride All` — without it, the URL shortcuts the app uses would break and every page except the homepage would show a "Not Found" error.

### Simply Technical

The **virtual host config** (`docs/vhost.conf`) points Apache at Laravel's `public/` directory — the only folder exposed to the web:

```apache
<VirtualHost *:80>
    ServerName onboarding.local
    DocumentRoot /var/www/onboarding-portal/public

    <Directory /var/www/onboarding-portal/public>
        AllowOverride All   # Required for .htaccess to work
        Require all granted
    </Directory>
</VirtualHost>
```

The `public/.htaccess` uses `mod_rewrite` to funnel all requests through `index.php` — Laravel's single entry point:

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^ index.php [L]
```

This means `/admin/staff/5/edit` doesn't correspond to a real file path on disk — `mod_rewrite` catches it and passes it to `index.php`, where Laravel's router dispatches it to the correct controller.

`mod_rewrite` must be enabled: `sudo a2enmod rewrite && sudo systemctl restart apache2`.

---

## 14. How to Get It Running Locally

### Plain English

To run this app on your own computer, you need a few things installed: PHP, the app's dependencies (handled automatically), a SQL Server database, and Apache. Once everything is installed, a couple of commands set up the database and create the starter accounts.

**The short version:**
1. Install PHP 8, Composer, Apache, SQL Server
2. Copy the config file and fill in your database password
3. Run two commands to set up the database
4. Visit the site in your browser

### Simply Technical

```bash
# 1. Install PHP dependencies
composer install

# 2. Copy the example environment file and configure it
cp .env.example .env
# Set DB_CONNECTION=sqlsrv, DB_HOST, DB_PORT=1433, DB_DATABASE, DB_USERNAME, DB_PASSWORD

# 3. Generate the application encryption key (used for sessions)
php artisan key:generate

# 4. Run migrations (creates all tables in order)
php artisan migrate

# 5. Seed the database (admin user, staff user, checklist tasks, sample record)
php artisan db:seed

# 6. Link the Apache vhost config and restart Apache
sudo cp docs/vhost.conf /etc/apache2/sites-available/onboarding.conf
sudo a2ensite onboarding.conf
sudo a2enmod rewrite
sudo systemctl restart apache2

# 7. Add to /etc/hosts for local domain resolution
echo "127.0.0.1 onboarding.local" | sudo tee -a /etc/hosts
```

**Visit:** `http://onboarding.local`

**Test credentials:**
| Account | Email | Password |
|---|---|---|
| Admin | admin@onboarding.local | password |
| Staff | staff@onboarding.local | password |

**To test the CSV sync:**
- Log in as Admin
- Go to `/admin/sync`
- Upload `storage/app/imports/sample_import.csv`
- Verify the summary page shows 2 inserted

**SQL Server via Docker (easier):**
```bash
docker run -e "ACCEPT_EULA=Y" -e "SA_PASSWORD=YourStrong@Passw0rd" \
  -p 1433:1433 mcr.microsoft.com/mssql/server:2022-latest
```

---

*HOW_IT_WORKS.md · Staff Onboarding Portal · Syed Rayyan Hussain · March 2026*
