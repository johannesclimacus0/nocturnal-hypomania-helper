<?php

namespace App\Providers;

use App\Models\Area;
use App\Models\Category;
use App\Models\NightSession;
use App\Models\NightSessionTask;
use App\Models\Task;
use App\Models\TaskType;
use App\Observers\AreaObserver;
use App\Observers\CategoryObserver;
use App\Observers\NightSessionObserver;
use App\Observers\NightSessionTaskObserver;
use App\Observers\TaskObserver;
use App\Observers\TaskTypeObserver;
use App\Policies\SessionPolicy;
use App\Policies\SessionTaskPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void {}

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(NightSession::class, SessionPolicy::class);
        Gate::policy(NightSessionTask::class, SessionTaskPolicy::class);

        Task::observe(TaskObserver::class);
        Area::observe(AreaObserver::class);
        Category::observe(CategoryObserver::class);
        TaskType::observe(TaskTypeObserver::class);
        NightSession::observe(NightSessionObserver::class);
        NightSessionTask::observe(NightSessionTaskObserver::class);
    }
}
