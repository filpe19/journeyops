<?php

namespace App\Providers;

use App\Enums\UserRole;
use App\Journey\JourneyLog;
use App\Journey\JourneyTracker;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(JourneyLog::class, fn () => new JourneyLog(config('journey.log_path')));

        $this->app->scoped(JourneyTracker::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('view-ops', fn (User $user) => $user->hasRole(UserRole::Admin));
    }
}
