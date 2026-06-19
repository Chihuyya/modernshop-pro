<?php

namespace App\Providers;

use App\Models\Produk;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

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
        Route::bind('product', function ($value) {
            return Produk::query()->where('id_produk', $value)->firstOrFail();
        });

        Vite::prefetch(concurrency: 3);
    }
}
