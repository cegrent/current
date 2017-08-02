<?php

namespace Ceghirepro\Current\Providers;

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
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
      $this->app->bind('Current',function(){
          return new \Ceghirepro\Current\CurrentAPI;
      });
    }
}
