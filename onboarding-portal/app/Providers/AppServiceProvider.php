<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Models\StaffRecord;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Gate: 'admin' — true if the user has the Admin role
        Gate::define('admin', function (User $user): bool {
            return $user->role === 'Admin';
        });

        // Gate: 'view-own-record' — Admin or the staff member who owns the record
        Gate::define('view-own-record', function (User $user, StaffRecord $record): bool {
            return $user->role === 'Admin' || (int) $user->id === (int) $record->user_id;
        });
    }
}
