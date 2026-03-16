<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use App\Models\AuditLog;

class AuditLogController extends Controller
{
    public function index()
    {
        Gate::authorize('admin');

        $logs = AuditLog::with('user')
            ->orderByDesc('created_at')
            ->paginate(50);

        return view('admin.audit-log', compact('logs'));
    }
}
