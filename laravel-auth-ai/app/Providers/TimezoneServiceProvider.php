<?php

namespace App\Providers;

use App\Services\TimezoneService;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class TimezoneServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Singleton agar satu instance dipakai di seluruh request
        $this->app->singleton(TimezoneService::class);
    }

    public function boot(): void
    {
        $this->registerBladeDirectives();
    }

    private function registerBladeDirectives(): void
    {
        /**
         * @localtime($model->created_at)
         * Output: "21 Mar 2026, 18:30"
         * Tampilkan waktu lokal user dengan format default.
         */
        Blade::directive('localtime', function (string $expression) {
            return "<?php echo app(\App\Services\TimezoneService::class)->format({$expression}); ?>";
        });

        /**
         * @localtimef($model->created_at, 'd/m/Y H:i')
         * @localtimef($model->created_at, 'l, d F Y')
         * Output: "Sabtu, 21 Maret 2026"
         * Tampilkan waktu lokal user dengan format kustom.
         */
        Blade::directive('localtimef', function (string $expression) {
            // Pisahkan argumen pertama (carbon) dan kedua (format)
            [$date, $format] = array_map('trim', explode(',', $expression, 2));

            return "<?php echo app(\App\Services\TimezoneService::class)->format({$date}, {$format}); ?>";
        });

        /**
         * @humanstime($model->created_at)
         * Output: "5 menit yang lalu"
         * Waktu relatif dalam timezone lokal user.
         */
        Blade::directive('humanstime', function (string $expression) {
            return "<?php echo app(\App\Services\TimezoneService::class)->diffForHumans({$expression}); ?>";
        });

        /**
         * @timezone
         * Output: "Asia/Jakarta"
         * Tampilkan nama timezone aktif user.
         */
        Blade::directive('timezone', function () {
            return "<?php echo app(\App\Services\TimezoneService::class)->getUserTimezone(); ?>";
        });
    }
}
