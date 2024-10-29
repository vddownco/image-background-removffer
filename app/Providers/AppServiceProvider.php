<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Codewithkyrian\Transformers\Transformers;
use Codewithkyrian\Transformers\Utils\ImageDriver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Transformers::setup()->setImageDriver(ImageDriver::IMAGICK);
    }
}
