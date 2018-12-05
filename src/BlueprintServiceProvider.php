<?php

namespace PHPJuice\Blueprint;

use Illuminate\Support\ServiceProvider;

class BlueprintServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'phpjuice');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'phpjuice');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/blueprint.php', 'blueprint');

        // Register the service the package provides.
        $this->app->singleton('blueprint', function ($app) {
            return new Blueprint;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['blueprint'];
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole()
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__.'/../config/blueprint.php' => config_path('blueprint.php'),
        ], 'blueprint.config');

        // Publishing the views.
        /*$this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/phpjuice'),
        ], 'blueprint.views');*/

        // Publishing assets.
        /*$this->publishes([
            __DIR__.'/../resources/assets' => public_path('vendor/phpjuice'),
        ], 'blueprint.views');*/

        // Publishing the translation files.
        /*$this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/phpjuice'),
        ], 'blueprint.views');*/

        // Registering package commands.
        $this->commands([
            Commands\BlueprintCommand::class,
            Commands\BlueprintMakeCommand::class,
            Commands\BlueprintGenerateCommand::class,
            Commands\ModelCommand::class,
            Commands\RequestCommand::class,
            Commands\ResourceCommand::class,
            Commands\MigrationCommand::class,
            Commands\APIControllerCommand::class,
        ]);
    }
}
