<?php

namespace Jsefton\MigrateEnvironments;

use Illuminate\Support\ServiceProvider;
use Jsefton\MigrateEnvironments\Console\MigrateEnv;

class MigrateEnvironmentsProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        // Register the command
        if ($this->app->runningInConsole()) {
            $this->commands([
                MigrateEnv::class
            ]);
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
