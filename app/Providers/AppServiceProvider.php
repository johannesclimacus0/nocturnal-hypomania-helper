<?php

namespace App\Providers;

use App\Models\NightSession;
use App\Models\NightSessionTask;
use App\Observers\SessionObserver;
use App\Policies\SessionPolicy;
use App\Policies\SessionTaskPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(NightSession::class, SessionPolicy::class);
        Gate::policy(NightSessionTask::class, SessionTaskPolicy::class);

        NightSession::observe(SessionObserver::class);
    }
}
