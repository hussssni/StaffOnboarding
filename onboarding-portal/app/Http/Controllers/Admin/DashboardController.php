<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use App\Models\StaffRecord;
use App\Models\AuditLog;

class DashboardController extends Controller
{
    public function index()
    {
        // Block non-admins — throws 403 if user is not Admin
        Gate::authorize('admin');

        // Count staff records grouped by status
        $statusCounts = StaffRecord::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $totalStaff = StaffRecord::count();

        // 10 most recent audit log entries with the user who made the change
        $recentActivity = AuditLog::with('user')
            ->orderByDesc('created_at')
            ->take(10)
            ->get();

        return view('admin.dashboard', compact('statusCounts', 'totalStaff', 'recentActivity'));
    }
}
