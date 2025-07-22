<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Blade;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Define Gates for role-based access control
        Gate::define('admin', function ($user) {
            return $user->role === 'admin';
        });

        Gate::define('dosen', function ($user) {
            return $user->role === 'dosen';
        });

        Gate::define('admin-dosen', function ($user) {
            return in_array($user->role, ['admin', 'dosen']);
        });

        Gate::define('mahasiswa', function ($user) {
            return $user->role === 'mahasiswa';
        });

        // Define custom Blade directives for easier template usage
        Blade::if('admin', function () {
            return auth()->check() && auth()->user()->role === 'admin';
        });

        Blade::if('dosen', function () {
            return auth()->check() && auth()->user()->role === 'dosen';
        });

        Blade::if('adminOrDosen', function () {
            return auth()->check() && in_array(auth()->user()->role, ['admin', 'dosen']);
        });

        Blade::if('mahasiswa', function () {
            return auth()->check() && auth()->user()->role === 'mahasiswa';
        });

        // Additional role checking blade directive
        Blade::if('role', function ($role) {
            return auth()->check() && auth()->user()->role === $role;
        });

        // Multiple roles checking
        Blade::if('hasAnyRole', function (...$roles) {
            return auth()->check() && in_array(auth()->user()->role, $roles);
        });
    }
}