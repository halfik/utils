<?php

namespace Netinteractive\Utils;

use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\AliasLoader;


class UtilsServiceProvider extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;


    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {

    }


    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {;
        $this->app->bind('utils', function () {
            return new Utils();
        });

        $this->app->booting(function()
        {
            AliasLoader::getInstance()->alias('Utils','Netinteractive\Utils\Facades\UtilsFacade');
        });

    }


    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array();
    }


}
