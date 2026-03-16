@extends('layouts.app')

@section('title', 'My Dashboard')

@section('content')
<h2 class="mb-4">Welcome, {{ auth()->user()->name }}</h2>

<div class="row g-3 mb-4">
    {{-- Profile summary card --}}
    <div class="col-md-5">
        <div class="card shadow-sm h-100">
            <div class="card-header">My Profile</div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-5">Name</dt>
                    <dd class="col-sm-7">{{ $record->full_name }}</dd>

                    <dt class="col-sm-5">Email</dt>
                    <dd class="col-sm-7">{{ $record->email }}</dd>

                    <dt class="col-sm-5">Department</dt>
                    <dd class="col-sm-7">{{ $record->department }}</dd>

                    <dt class="col-sm-5">Start Date</dt>
                    <dd class="col-sm-7">{{ $record->start_date }}</dd>

                    <dt class="col-sm-5">Status</dt>
                    <dd class="col-sm-7">
                        <span class="badge
                            @if($record->status === 'Active')     bg-success
                            @elseif($record->status === 'Pending') bg-warning text-dark
                            @else                                  bg-secondary
                            @endif">
                            {{ $record->status }}
                        </span>
                    </dd>
                </dl>
            </div>
        </div>
    </div>

    {{-- Checklist progress card --}}
    <div class="col-md-7">
        <div class="card shadow-sm h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Onboarding Checklist</span>
                <a href="{{ route('staff.checklist') }}" class="btn btn-sm btn-outline-dark">View Full Checklist</a>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>Progress</span>
                    <strong>{{ $progress }}%</strong>
                </div>
                <div class="progress mb-4" style="height: 24px;">
                    <div class="progress-bar bg-success"
                         style="width: {{ $progress }}%"
                         role="progressbar"
                         aria-valuenow="{{ $progress }}"
                         aria-valuemin="0"
                         aria-valuemax="100">
                        {{ $progress }}%
                    </div>
                </div>

                <ul class="list-group list-group-flush">
                    @foreach($items as $item)
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        {{ $item->task->name }}
                        @if($item->is_complete)
                            <span class="badge bg-success">✓ Done</span>
                        @else
                            <span class="badge bg-warning text-dark">Pending</span>
                        @endif
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
