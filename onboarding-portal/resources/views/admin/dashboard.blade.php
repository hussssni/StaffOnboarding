@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<h2 class="mb-4">Admin Dashboard</h2>

{{-- Summary cards --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card text-center shadow-sm">
            <div class="card-body">
                <h1 class="display-5 fw-bold">{{ $totalStaff }}</h1>
                <p class="text-muted mb-0">Total Staff</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center shadow-sm border-warning">
            <div class="card-body">
                <h1 class="display-5 fw-bold text-warning">{{ $statusCounts['Pending'] ?? 0 }}</h1>
                <p class="text-muted mb-0">Pending</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center shadow-sm border-success">
            <div class="card-body">
                <h1 class="display-5 fw-bold text-success">{{ $statusCounts['Active'] ?? 0 }}</h1>
                <p class="text-muted mb-0">Active</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center shadow-sm border-secondary">
            <div class="card-body">
                <h1 class="display-5 fw-bold text-secondary">{{ $statusCounts['Offboarded'] ?? 0 }}</h1>
                <p class="text-muted mb-0">Offboarded</p>
            </div>
        </div>
    </div>
</div>

{{-- Recent activity --}}
<div class="card shadow-sm">
    <div class="card-header">Recent Activity</div>
    <div class="card-body p-0">
        <table class="table table-sm table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>When</th>
                    <th>Who</th>
                    <th>Action</th>
                    <th>Record ID</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentActivity as $log)
                <tr>
                    <td class="text-muted small">{{ $log->created_at->diffForHumans() }}</td>
                    <td>{{ $log->user->name ?? '—' }}</td>
                    <td>
                        <span class="badge bg-{{ $log->action === 'created' ? 'success' : ($log->action === 'deleted' ? 'danger' : 'primary') }}">
                            {{ $log->action }}
                        </span>
                        {{ $log->target_table }}
                    </td>
                    <td>#{{ $log->target_id }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-muted text-center py-3">No activity yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer text-end">
        <a href="{{ route('admin.audit-log') }}" class="btn btn-sm btn-outline-secondary">View full audit log →</a>
    </div>
</div>
@endsection
