@extends('layouts.app')

@section('title', 'Checklist — ' . $staff->full_name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2>Checklist — {{ $staff->full_name }}</h2>
        <p class="text-muted mb-0">{{ $staff->department }} · {{ $staff->status }}</p>
    </div>
    <a href="{{ route('admin.staff.index') }}" class="btn btn-outline-secondary">← Back to Staff</a>
</div>

{{-- Progress bar --}}
@php
    $total     = $items->count();
    $completed = $items->where('is_complete', true)->count();
    $percent   = $total > 0 ? round(($completed / $total) * 100) : 0;
@endphp

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between mb-1">
            <span class="fw-semibold">Overall Progress</span>
            <span>{{ $completed }} / {{ $total }} ({{ $percent }}%)</span>
        </div>
        <div class="progress" style="height: 20px;">
            <div class="progress-bar bg-success"
                 role="progressbar"
                 style="width: {{ $percent }}%"
                 aria-valuenow="{{ $percent }}"
                 aria-valuemin="0"
                 aria-valuemax="100">
                {{ $percent }}%
            </div>
        </div>
    </div>
</div>

{{-- Checklist items --}}
<div class="card shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width:50px;">#</th>
                    <th>Task</th>
                    <th>Status</th>
                    <th>Completed By</th>
                    <th>Completed At</th>
                    <th>Action</th>
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
                            <span class="badge bg-success">Complete</span>
                        @else
                            <span class="badge bg-warning text-dark">Pending</span>
                        @endif
                    </td>
                    <td>{{ $item->completedByUser->name ?? '—' }}</td>
                    <td>{{ $item->completed_at ? $item->completed_at->format('M j, Y g:ia') : '—' }}</td>
                    <td>
                        {{-- Toggle button — marks complete or reverts to incomplete --}}
                        <form method="POST"
                              action="{{ route('admin.staff.checklist.toggle', [$staff->id, $item->task_id]) }}">
                            @csrf
                            <button type="submit"
                                    class="btn btn-sm {{ $item->is_complete ? 'btn-outline-warning' : 'btn-outline-success' }}">
                                {{ $item->is_complete ? 'Mark Incomplete' : 'Mark Complete' }}
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
