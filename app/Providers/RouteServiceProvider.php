<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * The path to the "home" route for your application.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        //

        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();

        $this->mapWebRoutes();

        $this->mapBudgetRoutes();

        $this->mapOrganisasiRoutes();

        // add Fahmi 11 Mei 2020
        $this->mapSqiiRoutes();

        // add Dimas 27 Juni 2021
        $this->mapDwRoutes();

        // add Septiyan 21 Jan 2022
        $this->mapVouchermallRoutes();    
            
        // add Dimas 14 Maret 2022
        $this->mapPpRoutes();
        $this->mapsippRoutes();
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/web.php'));
    }

    /**
     * Define the "budgetRoutes" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapBudgetRoutes()
    {
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/budgetRoutes.php'));
    }

    protected function mapOrganisasiRoutes()
    {
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/organisasiRoutes.php'));
    }

    // add Septiyan 21 Jan 2022
    protected function mapVouchermallRoutes()
    {
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/vouchermallRoutes.php'));
    }

    // add Fahmi 11 mei 2020
    protected function mapSqiiRoutes()
    {
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/sqiiRoutes.php'));
    }

    // add Dimas 27 Jun 2021 untuk aplikasi Downtown
    protected function mapDwRoutes()
    {
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/dwRoutes.php'));
    }

    // add Dimas 14 Maret 2022 untuk aplikasi PPRS
    protected function mapPpRoutes()
    {
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/ppRoutes.php'));
    }

       // add Dimas 27 Jun 2021 untuk aplikasi Downtown
       protected function mapsippRoutes()
       {
           Route::middleware('web')
               ->namespace($this->namespace)
               ->group(base_path('routes/sippRoutes.php'));
       }
    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::prefix('api')
            ->middleware('api')
            ->namespace($this->namespace)
            ->group(base_path('routes/api.php'));
    }
}
