@extends('layouts.app')

@section('title', 'Add Staff Member')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0">Add New Staff Member</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.staff.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="full_name" class="form-label">Full Name</label>
                        <input type="text"
                               id="full_name"
                               name="full_name"
                               class="form-control @error('full_name') is-invalid @enderror"
                               value="{{ old('full_name') }}"
                               required>
                        @error('full_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email"
                               id="email"
                               name="email"
                               class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email') }}"
                               required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="department" class="form-label">Department</label>
                        <input type="text"
                               id="department"
                               name="department"
                               class="form-control @error('department') is-invalid @enderror"
                               value="{{ old('department') }}"
                               required>
                        @error('department')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date"
                               id="start_date"
                               name="start_date"
                               class="form-control @error('start_date') is-invalid @enderror"
                               value="{{ old('start_date') }}"
                               required>
                        @error('start_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="status" class="form-label">Status</label>
                        <select id="status" name="status" class="form-select">
                            @foreach(['Pending', 'Active', 'Offboarded'] as $s)
                                <option value="{{ $s }}" @selected(old('status', 'Pending') === $s)>
                                    {{ $s }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-dark">Create Record</button>
                        <a href="{{ route('admin.staff.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
