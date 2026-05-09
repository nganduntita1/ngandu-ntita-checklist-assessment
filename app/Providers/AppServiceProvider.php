<?php

namespace App\Providers;

use App\Models\ChecklistInstance;
use App\Models\ChecklistTemplate;
use App\Policies\InstancePolicy;
use App\Policies\ReportPolicy;
use App\Policies\TemplatePolicy;
use App\Repositories\Contracts\InstanceRepositoryInterface;
use App\Repositories\Contracts\ReportRepositoryInterface;
use App\Repositories\Contracts\TemplateRepositoryInterface;
use App\Repositories\InstanceRepository;
use App\Repositories\ReportRepository;
use App\Repositories\TemplateRepository;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(TemplateRepositoryInterface::class, TemplateRepository::class);
        $this->app->bind(InstanceRepositoryInterface::class, InstanceRepository::class);
        $this->app->bind(ReportRepositoryInterface::class, ReportRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register model policies
        Gate::policy(ChecklistTemplate::class, TemplatePolicy::class);
        Gate::policy(ChecklistInstance::class, InstancePolicy::class);

        // ReportPolicy has no dedicated model — register its gate directly
        Gate::define('viewAny-report', [ReportPolicy::class, 'viewAny']);
    }
}
