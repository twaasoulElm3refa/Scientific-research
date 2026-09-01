<?php

namespace App\Providers;

use App\interface\BooksInterface;
use App\interface\categoreyInterface;
use App\Repository\bookRepository;
use App\Repository\CategoreyRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(categoreyInterface::class, CategoreyRepository::class);
        $this->app->bind(BooksInterface::class, bookRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
