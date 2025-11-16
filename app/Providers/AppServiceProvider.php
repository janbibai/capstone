<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
        Gate::define('isAdmin', function ($user) {
        return $user->role === 'admin';
        });

        Gate::define('isStaff', function ($user) {
        return in_array($user->role, ['admin', 'staff']);
        });

        Gate::define('isPatient', function ($user) {
        return $user->role === 'patient';
    });
    }
}
