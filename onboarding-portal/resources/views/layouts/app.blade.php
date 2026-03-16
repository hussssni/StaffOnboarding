<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Staff Onboarding Portal')</title>
    <!-- Bootstrap 5 via CDN — no build tool required -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .navbar-brand { font-weight: 700; }
        .badge-Pending    { background-color: #ffc107; color: #000; }
        .badge-Active     { background-color: #198754; }
        .badge-Offboarded { background-color: #6c757d; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="{{ route('dashboard') }}">Staff Onboarding Portal</a>

        @auth
        <div class="navbar-nav ms-auto">
            {{-- Admin-only nav items --}}
            @can('admin')
                <a class="nav-link" href="{{ route('admin.dashboard') }}">Dashboard</a>
                <a class="nav-link" href="{{ route('admin.staff.index') }}">Staff</a>
                <a class="nav-link" href="{{ route('admin.sync') }}">Sync</a>
                <a class="nav-link" href="{{ route('admin.audit-log') }}">Audit Log</a>
            @endcan

            {{-- Staff-only nav items --}}
            @cannot('admin')
                <a class="nav-link" href="{{ route('staff.dashboard') }}">My Dashboard</a>
                <a class="nav-link" href="{{ route('staff.record') }}">My Record</a>
                <a class="nav-link" href="{{ route('staff.checklist') }}">My Checklist</a>
            @endcannot

            <span class="nav-link text-secondary">{{ auth()->user()->name }} ({{ auth()->user()->role }})</span>

            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-link nav-link text-danger p-0 ms-2">Logout</button>
            </form>
        </div>
        @endauth
    </div>
</nav>

<div class="container">
    {{-- Flash success message --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Validation errors --}}
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @yield('content')
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
