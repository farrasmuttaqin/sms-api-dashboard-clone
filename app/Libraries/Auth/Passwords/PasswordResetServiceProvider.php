<?php

namespace Firstwap\SmsApiDashboard\Libraries\Auth\Passwords;

use Firstwap\SmsApiDashboard\Libraries\Auth\Passwords\PasswordBrokerManager;
use Illuminate\Auth\Passwords\PasswordResetServiceProvider as ServiceProvider;

class PasswordResetServiceProvider extends ServiceProvider
{

    /**
     * Register the password broker instance.
     *
     * @return void
     */
    protected function registerPasswordBroker()
    {
        $this->app->singleton('auth.password', function ($app) {
            return new PasswordBrokerManager($app);
        });

        $this->app->bind('auth.password.broker', function ($app) {
            return $app->make('auth.password')->broker();
        });
    }

}
