<?php

namespace App\Providers;

use App\Repository\Eloquent\PdfChunkRepository;
use App\Repository\Eloquent\PdfFileRepository;
use App\Repository\Eloquent\Repository;
use App\Repository\Eloquent\UserRepository;
use App\Repository\PdfChunkRepositoryInterface;
use App\Repository\PdfFileRepositoryInterface;
use App\Repository\RepositoryInterface;
use App\Repository\UserRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(RepositoryInterface::class,Repository::class);
        $this->app->singleton(UserRepositoryInterface::class,UserRepository::class);
        $this->app->singleton(PdfFileRepositoryInterface::class,PdfFileRepository::class);
        $this->app->singleton(PdfChunkRepositoryInterface::class,PdfChunkRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
