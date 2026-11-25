<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\URL;
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
        \Carbon\Carbon::setLocale('id');

        if (config('app.env') !== 'local') {
            URL::forceScheme('https');
        }

        \Illuminate\Support\Facades\RateLimiter::for('login', function (\Illuminate\Http\Request $request) {
            $username = (string) $request->input('username', '');
            $throttleKey = \Illuminate\Support\Str::transliterate(\Illuminate\Support\Str::lower($username) . '|' . $request->ip());

            return \Illuminate\Cache\RateLimiting\Limit::perMinute(5)->by($throttleKey)->response(function () {
                return back()->withErrors([
                    'username' => 'Terlalu banyak percobaan masuk yang gagal. Silakan tunggu 1 menit sebelum mencoba kembali.',
                ])->onlyInput('username');
            });
        });

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
