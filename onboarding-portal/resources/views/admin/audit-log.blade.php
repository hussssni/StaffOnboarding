@extends('layouts.app')

@section('title', 'Audit Log')

@section('content')
<h2 class="mb-1">Audit Log</h2>
<p class="text-muted mb-4">
    Every create, update, and delete on staff records is recorded here. 
    NOTE: This log is permanent, so entries cannot be edited or deleted.
</p>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <table class="table table-sm table-hover mb-0">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>When</th>
                    <th>Who</th>
                    <th>Action</th>
                    <th>Table</th>
                    <th>Record ID</th>
                    <th>Data</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td class="text-muted">{{ $log->id }}</td>
                    <td class="text-muted small" title="{{ $log->created_at }}">
                        {{ $log->created_at->format('M j, Y g:ia') }}
                    </td>
                    <td>{{ $log->user->name ?? '(deleted)' }}</td>
                    <td>
                        <span class="badge bg-{{ $log->action === 'created' ? 'success' : ($log->action === 'deleted' ? 'danger' : 'primary') }}">
                            {{ $log->action }}
                        </span>
                    </td>
                    <td><code>{{ $log->target_table }}</code></td>
                    <td>#{{ $log->target_id }}</td>
                    <td>
                        {{-- Truncate the JSON to avoid cluttering the table --}}
                        <span class="text-muted small font-monospace"
                              title="{{ $log->changed_data }}">
                            {{ Str::limit($log->changed_data, 80) }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">No audit entries yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">
    {{ $logs->links() }}
</div>
@endsection
