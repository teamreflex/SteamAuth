<?php

namespace Reflex\SteamAuth\Laravel;

use Illuminate\Support\ServiceProvider;
use Reflex\SteamAuth\SteamAuth;

class SteamAuthServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('steamauth', function ($app) {
            return new SteamAuth();
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['steamlogin'];
    }
}
