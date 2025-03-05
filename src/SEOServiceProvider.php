<?php

namespace Seodably\SEO;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Blade;
use Seodably\SEO\Console\Commands\InstallCommand;
use Seodably\SEO\Http\Middleware\SEOMiddleware;

class SEOServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Fusionner la configuration
        $this->mergeConfigFrom(
            __DIR__ . '/../config/seo.php', 'seo'
        );

        // Enregistrer les services
        $this->app->singleton('seo', function ($app) {
            return new Services\SEOService();
        });

        $this->app->singleton('seo.ai', function ($app) {
            return new Services\AIService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Publier la configuration
        $this->publishes([
            __DIR__ . '/../config/seo.php' => config_path('seo.php'),
        ], 'seo-config');

        // Publier les migrations
        $this->publishes([
            __DIR__ . '/../database/migrations/' => database_path('migrations'),
        ], 'seo-migrations');

        // Publier les assets Vue.js
        $this->publishes([
            __DIR__ . '/../resources/js/' => resource_path('js/vendor/seo'),
        ], 'seo-assets');

        // Charger les migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Charger les routes
        $this->loadRoutes();

        // Enregistrer les commandes
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
            ]);
        }

        // Enregistrer le middleware
        $this->app['router']->aliasMiddleware('seo', SEOMiddleware::class);
    }

    /**
     * Charge les routes du package.
     */
    protected function loadRoutes(): void
    {
        Route::group($this->routeConfiguration(), function () {
            $this->loadRoutesFrom(__DIR__ . '/../routes/seo.php');
        });

        // Routes API
        Route::group($this->apiRouteConfiguration(), function () {
            $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        });
    }

    /**
     * Configuration des routes web.
     */
    protected function routeConfiguration(): array
    {
        return [
            'prefix' => config('seo.ui.dashboard_route', 'admin/seo'),
            'middleware' => ['web', 'auth'],
            'namespace' => 'Seodably\SEO\Http\Controllers',
        ];
    }

    /**
     * Configuration des routes API.
     */
    protected function apiRouteConfiguration(): array
    {
        return [
            'prefix' => 'api/seo',
            'middleware' => ['api'],
            'namespace' => 'Seodably\SEO\Http\Controllers',
        ];
    }
} 