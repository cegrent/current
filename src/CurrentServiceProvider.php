<?php

namespace Cegrent\Current;

use Illuminate\Support\ServiceProvider;

class CurrentServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
      $this->publishes([__DIR__.'/Config/config.php' => config_path('current.php')]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
      $this->app->bind('Current',function(){
          return new \Cegrent\Current\CurrentAPI;
      });
    }
}
