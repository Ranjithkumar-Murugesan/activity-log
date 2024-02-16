<?php

namespace Ical\ActivityLog;

use Illuminate\Support\ServiceProvider;

class ActivityLogServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register any bindings or services here
    }

    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
    }
}
