<?php

namespace Modules\Sampark\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

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
        $this->registerWebRoutes();
        $this->registerApiRoutes();
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
    protected function registerWebRoutes()
    {
        $webRoutes = __DIR__ . '/../Routes/web.php';

        if (file_exists($webRoutes)) {
            Route::middleware('web')
                ->group($webRoutes);
        }
    }

    protected function registerApiRoutes()
    {
        $apiRoutes = __DIR__ . '/../Routes/api.php';

        if (file_exists($apiRoutes)) {
            Route::prefix('api/sampark')
                ->middleware('api')
                ->group($apiRoutes);
        }
    }
}
