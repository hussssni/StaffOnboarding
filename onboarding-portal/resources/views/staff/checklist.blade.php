@extends('layouts.app')

@section('title', 'My Checklist')

@section('content')
<h2 class="mb-1">My Onboarding Checklist</h2>
<p class="text-muted mb-4">Track your onboarding progress below. Contact your administrator to mark items complete.</p>

{{-- Progress bar --}}
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between mb-1">
            <span class="fw-semibold">Overall Progress</span>
            <span>{{ $progress }}%</span>
        </div>
        <div class="progress" style="height: 20px;">
            <div class="progress-bar bg-success"
                 style="width: {{ $progress }}%"
                 role="progressbar"
                 aria-valuenow="{{ $progress }}"
                 aria-valuemin="0"
                 aria-valuemax="100">
                {{ $progress }}%
            </div>
        </div>
    </div>
</div>

{{-- Task list (read-only for staff) --}}
<div class="card shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width:40px;">#</th>
                    <th>Task</th>
                    <th>Status</th>
                    <th>Completed</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                <tr class="{{ $item->is_complete ? 'table-success' : '' }}">
                    <td>{{ $item->task->sort_order }}</td>
                    <td>
                        <strong>{{ $item->task->name }}</strong>
                        @if($item->task->description)
                            <br><small class="text-muted">{{ $item->task->description }}</small>
                        @endif
                    </td>
                    <td>
                        @if($item->is_complete)
                            <span class="badge bg-success">✓ Complete</span>
                        @else
                            <span class="badge bg-warning text-dark">Pending</span>
                        @endif
                    </td>
                    <td class="text-muted small">
                        {{ $item->completed_at ? $item->completed_at->format('M j, Y') : '—' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
