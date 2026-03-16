@extends('layouts.app')

@section('title', 'All Staff Records')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Staff Records</h2>
    <a href="{{ route('admin.staff.create') }}" class="btn btn-dark">+ Add Staff</a>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Department</th>
                    <th>Start Date</th>
                    <th>Status</th>
                    <th>Source</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($staffRecords as $staff)
                <tr>
                    <td>{{ $staff->id }}</td>
                    <td>{{ $staff->full_name }}</td>
                    <td>{{ $staff->email }}</td>
                    <td>{{ $staff->department }}</td>
                    <td>{{ $staff->start_date }}</td>
                    <td>
                        <span class="badge
                            @if($staff->status === 'Active')     bg-success
                            @elseif($staff->status === 'Pending') bg-warning text-dark
                            @else                                 bg-secondary
                            @endif">
                            {{ $staff->status }}
                        </span>
                    </td>
                    <td><span class="badge bg-light text-dark border">{{ $staff->source }}</span></td>
                    <td>
                        <a href="{{ route('admin.staff.edit', $staff->id) }}"
                           class="btn btn-sm btn-outline-primary">Edit</a>
                        <a href="{{ route('admin.staff.checklist', $staff->id) }}"
                           class="btn btn-sm btn-outline-secondary">Checklist</a>
                        <form method="POST"
                              action="{{ route('admin.staff.destroy', $staff->id) }}"
                              class="d-inline"
                              onsubmit="return confirm('Delete {{ $staff->full_name }}? This cannot be undone.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">
                        No staff records yet. <a href="{{ route('admin.staff.create') }}">Add one →</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">
    {{ $staffRecords->links() }}
</div>
@endsection
