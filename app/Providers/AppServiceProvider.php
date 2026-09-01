<?php

namespace App\Providers;

use App\Contracts\GoogleDrive;
use App\interface\BooksInterface;
use App\interface\categoreyInterface;
use App\Repository\bookRepository;
use App\Repository\CategoreyRepository;
use App\Services\GoogleDriveService;
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
        $this->app->bind(GoogleDrive::class, GoogleDriveService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
