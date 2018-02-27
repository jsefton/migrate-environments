<?php

namespace JSefton\MigrateEnv;

use Illuminate\Support\ServiceProvider;
use JSefton\MigrateEnv\Console\MigrateEnv;

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

        // Publish config file to config folder
        $this->publishes([
            __DIR__.'/../config/migrate-env.php' => config_path('migrate-env.php')
        ], 'migrate-env');
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
