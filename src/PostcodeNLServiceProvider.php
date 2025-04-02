<?php

namespace Cubenl\PostcodeNL;

use Illuminate\Support\ServiceProvider;

/**
 * PostcodeNLServiceProvider
 *
 * @version 1.0
 */
class PostcodeNLServiceProvider extends ServiceProvider
{
    /**
     * The name of the package
     */
    public static string $name = 'postcode-nl:service';

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/postcode-nl.php',
            'postcode-nl'
        );

        $this->app->singleton(self::$name, function () {
            return new PostcodeNL;
        });
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'postcode-nl');
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');

        $this->publishes([
            __DIR__ . '/../config/postcode-nl.php' => config_path('postcode-nl.php'),
        ], 'postcode-nl-config');

        $this->publishes([
            __DIR__ . '/../lang' => resource_path('lang/vendor/postcode-nl'),
        ], 'postcode-nl-translations');

        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'postcode-nl-migrations');
    }
}
