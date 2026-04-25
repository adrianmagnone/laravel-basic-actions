<?php

namespace Aiglos\Lba;

use Illuminate\Support\ServiceProvider;

class LbaServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/lba.php', 'lba');
    }

    /** 
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/lba.php' => config_path('lba.php'),
            ], 'config');
        }
    }
}