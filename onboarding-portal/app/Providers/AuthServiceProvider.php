<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Models\StaffRecord;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     * We use Gates instead of Policies for explicitness and auditability.
     */
    protected $policies = [];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        /*
        |----------------------------------------------------------------------
        | Gate: 'admin'
        |----------------------------------------------------------------------
        | Returns true if the authenticated user has the Admin role.
        | Used in every admin controller via Gate::authorize('admin').
        | Used in Blade templates via @can('admin') ... @endcan.
        */
        Gate::define('admin', function (User $user): bool {
            return $user->role === 'Admin';
        });

        /*
        |----------------------------------------------------------------------
        | Gate: 'view-own-record'
        |----------------------------------------------------------------------
        | Returns true if the user is an Admin OR if the staff record belongs
        | to the authenticated user. Used on staff-facing record views.
        */
        Gate::define('view-own-record', function (User $user, StaffRecord $record): bool {
            return $user->role === 'Admin' || $user->id === $record->user_id;
        });
    }
}
