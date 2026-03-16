@extends('layouts.app')

@section('title', 'CSV Sync Utility')

@section('content')
<h2 class="mb-1">CSV Sync Utility</h2>
<p class="text-muted mb-4">
    Upload a CSV file to import or update staff records.
    Existing records are matched by email — new rows are inserted, changed rows are updated, identical rows are skipped.
</p>

{{-- Sync results (shown after a run) --}}
@if(session('sync_results'))
    @php $r = session('sync_results'); @endphp
    <div class="alert alert-info">
        <strong>Sync complete</strong> — {{ session('sync_filename') }}<br>
        <span class="badge bg-success">{{ $r['inserted'] }} inserted</span>
        <span class="badge bg-primary ms-1">{{ $r['updated'] }} updated</span>
        <span class="badge bg-secondary ms-1">{{ $r['skipped'] }} skipped</span>
    </div>
@endif

{{-- Upload form --}}
<div class="row">
    <div class="col-md-6">
        <div class="card shadow-sm mb-4">
            <div class="card-header">Upload CSV File</div>
            <div class="card-body">
                <form method="POST"
                      action="{{ route('admin.sync.run') }}"
                      enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="csv_file" class="form-label">CSV File</label>
                        <input type="file"
                               id="csv_file"
                               name="csv_file"
                               class="form-control @error('csv_file') is-invalid @enderror"
                               accept=".csv">
                        @error('csv_file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">
                            Required columns: <code>full_name, email, department, start_date, status</code>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-dark">Run Sync</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card shadow-sm mb-4">
            <div class="card-header">Expected CSV Format</div>
            <div class="card-body">
                <pre class="mb-0 small bg-light p-3 rounded">full_name,email,department,start_date,status
Jane Smith,jane.smith@example.com,Research,2026-04-01,Pending
Marcus Lee,marcus.lee@example.com,Events,2026-04-07,Pending</pre>
                <p class="mt-2 mb-0 text-muted small">
                    A sample file is at <code>storage/app/imports/sample_import.csv</code>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
