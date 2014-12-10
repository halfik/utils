<?php

namespace Netinteractive\Utils;

use Illuminate\Support\ServiceProvider;


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
        $this->package('netinteractive/utils');
    }


    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        \App::bind('Utils', function($app)
        {
            return new \Netinteractive\Utils\Utils;
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
