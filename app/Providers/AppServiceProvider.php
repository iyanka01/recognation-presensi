<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\PengaturanLembaga;

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
        View::composer('*', function ($view) {
            try {
                $globalLembaga = PengaturanLembaga::first();
            } catch (\Exception $e) {
                $globalLembaga = null;
            }
            $view->with('globalLembaga', $globalLembaga);
        });
    }
}
