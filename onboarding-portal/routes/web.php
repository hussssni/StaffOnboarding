<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\ChecklistController;
use App\Http\Controllers\Admin\SyncController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Staff\StaffDashboardController;

// ---------------------------------------------------------
// Public routes — no login required
// ---------------------------------------------------------
Route::get('/login', [LoginController::class, 'showForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);

// ---------------------------------------------------------
// Shared auth route — redirects based on role
// ---------------------------------------------------------
Route::middleware('auth')->get('/dashboard', function () {
    return auth()->user()->role === 'Admin'
        ? redirect()->route('admin.dashboard')
        : redirect()->route('staff.dashboard');
})->name('dashboard');

Route::middleware('auth')->post('/logout', [LoginController::class, 'logout'])->name('logout');

// ---------------------------------------------------------
// Admin routes — auth middleware + Gate::authorize('admin')
// inside each controller method
// ---------------------------------------------------------
Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Staff CRUD
    Route::get('/staff',                    [StaffController::class, 'index'])->name('staff.index');
    Route::get('/staff/create',             [StaffController::class, 'create'])->name('staff.create');
    Route::post('/staff',                   [StaffController::class, 'store'])->name('staff.store');
    Route::get('/staff/{id}/edit',          [StaffController::class, 'edit'])->name('staff.edit');
    Route::put('/staff/{id}',               [StaffController::class, 'update'])->name('staff.update');
    Route::delete('/staff/{id}',            [StaffController::class, 'destroy'])->name('staff.destroy');

    // Checklist management
    Route::get('/staff/{id}/checklist',             [ChecklistController::class, 'show'])->name('staff.checklist');
    Route::post('/staff/{id}/checklist/{task_id}',  [ChecklistController::class, 'toggle'])->name('staff.checklist.toggle');

    // CSV sync utility
    Route::get('/sync',  [SyncController::class, 'show'])->name('sync');
    Route::post('/sync', [SyncController::class, 'run'])->name('sync.run');

    // Audit log viewer
    Route::get('/audit-log', [AuditLogController::class, 'index'])->name('audit-log');
});

// ---------------------------------------------------------
// Staff routes — auth middleware only (Gate check inside controller)
// ---------------------------------------------------------
Route::middleware('auth')->prefix('staff')->name('staff.')->group(function () {
    Route::get('/dashboard', [StaffDashboardController::class, 'index'])->name('dashboard');
    Route::get('/record',    [StaffDashboardController::class, 'record'])->name('record');
    Route::get('/checklist', [StaffDashboardController::class, 'checklist'])->name('checklist');
});
