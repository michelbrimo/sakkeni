<?php

namespace App\Providers;

use App\Models\PhysicalStatusType;
use App\Models\PropertyType;
use App\Models\SellType;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        Route::bind('physical_status_type', function ($value) {
            request()->route()->setParameter('physical_status_type', $value);
            return PhysicalStatusType::where('name', $value)->firstOrFail()->id;
        });

        Route::bind('property_type_id', function ($value) {
            request()->route()->setParameter('property_type_id', $value);
            return PropertyType::where('name', $value)->firstOrFail()->id;
        });

        Route::bind('sell_type_id', function ($value) {
            request()->route()->setParameter('sell_type_id', $value);
            return SellType::where('name', $value)->firstOrFail()->id;
        });

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
}
