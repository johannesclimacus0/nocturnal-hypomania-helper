<?php

namespace App\Providers;

use App\Models\NightSession;
use App\Models\NightSessionTask;
use App\Policies\SessionPolicy;
use App\Policies\SessionTaskPolicy;
use App\Services\TaskSelection\Strategies\ShortestTaskStrategy;
use App\Services\TaskSelection\Strategies\TaskSelectionStrategy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(TaskSelectionStrategy::class, ShortestTaskStrategy::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(NightSession::class, SessionPolicy::class);
        Gate::policy(NightSessionTask::class, SessionTaskPolicy::class);

    }
}
