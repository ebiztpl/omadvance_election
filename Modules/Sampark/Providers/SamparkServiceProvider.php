<?php

namespace Modules\Sampark\Providers;

use Illuminate\Support\ServiceProvider;

class SamparkServiceProvider extends ServiceProvider
{
    protected $moduleName = 'Sampark';
    protected $moduleNameLower = 'sampark';

    /**
     * Boot the application events.
     */
    public function boot()
    {
        $this->registerViews();
        $this->registerRoutes();
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        //
    }

    /**
     * Register views.
     */
    protected function registerViews()
    {
        $sourcePath = __DIR__ . '/../Resources/views';
        $this->loadViewsFrom($sourcePath, $this->moduleNameLower);
    }

    /**
     * Register module routes.
     */
    protected function registerRoutes()
    {
        $routesPath = __DIR__ . '/../Routes/web.php';
        if (file_exists($routesPath)) {
            $this->loadRoutesFrom($routesPath);
        }
    }
}
