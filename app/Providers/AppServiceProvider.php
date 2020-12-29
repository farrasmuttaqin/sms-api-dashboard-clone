<?php

namespace Firstwap\SmsApiDashboard\Providers;

use Firstwap\SmsApiDashboard\Entities\User;
use Firstwap\SmsApiDashboard\Observers\UserObserver;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        User::observe(UserObserver::class);

        if (env('APP_ENV') !== 'production') {
            Schema::defaultStringLength(191); // @codeCoverageIgnore
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
