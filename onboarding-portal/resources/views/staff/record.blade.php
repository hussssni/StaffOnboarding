@extends('layouts.app')

@section('title', 'My Record')

@section('content')
<h2 class="mb-4">My Record</h2>

<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card shadow-sm">
            <div class="card-header">Staff Profile (Read-Only)</div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-4">Full Name</dt>
                    <dd class="col-sm-8">{{ $record->full_name }}</dd>

                    <dt class="col-sm-4">Email</dt>
                    <dd class="col-sm-8">{{ $record->email }}</dd>

                    <dt class="col-sm-4">Department</dt>
                    <dd class="col-sm-8">{{ $record->department }}</dd>

                    <dt class="col-sm-4">Start Date</dt>
                    <dd class="col-sm-8">{{ $record->start_date }}</dd>

                    <dt class="col-sm-4">Status</dt>
                    <dd class="col-sm-8">
                        <span class="badge
                            @if($record->status === 'Active')     bg-success
                            @elseif($record->status === 'Pending') bg-warning text-dark
                            @else                                  bg-secondary
                            @endif">
                            {{ $record->status }}
                        </span>
                    </dd>

                    <dt class="col-sm-4">Record ID</dt>
                    <dd class="col-sm-8 text-muted">#{{ $record->id }}</dd>

                    <dt class="col-sm-4">Created</dt>
                    <dd class="col-sm-8 text-muted">{{ $record->created_at->format('M j, Y') }}</dd>
                </dl>

                <p class="text-muted small mb-0">
                    Need to update your information? Contact your HR administrator.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
