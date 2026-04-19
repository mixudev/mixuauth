<?php

namespace App\Modules\Timezone;

use App\Modules\Timezone\Services\TimezoneService;
use Carbon\Carbon;
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
        $this->registerCarbonMacros();
        $this->registerBladeDirectives();
    }

    private function registerCarbonMacros(): void
    {
        Carbon::macro('toLocal', function (?string $timezone = null) {
            /** @var Carbon $this */
            return app(TimezoneService::class)->toLocal($this, $timezone);
        });

        Carbon::macro('localFormat', function (string $format = 'd M Y, H:i', ?string $timezone = null) {
            /** @var Carbon $this */
            return app(TimezoneService::class)->format($this, $format, $timezone);
        });
    }

    private function registerBladeDirectives(): void
    {
        /**
         * @localtime($model->created_at)
         * Output: "21 Mar 2026, 18:30"
         * Tampilkan waktu lokal user dengan format default.
         */
        Blade::directive('localtime', function (string $expression) {
            return "<?php echo app(\App\Modules\Timezone\Services\TimezoneService::class)->format({$expression}); ?>";
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

            return "<?php echo app(\App\Modules\Timezone\Services\TimezoneService::class)->format({$date}, {$format}); ?>";
        });

        /**
         * @localdate($model->created_at)
         * Output: "21 Mar 2026"
         */
        Blade::directive('localdate', function (string $expression) {
            return "<?php echo app(\App\Modules\Timezone\Services\TimezoneService::class)->format({$expression}, 'd M Y'); ?>";
        });

        /**
         * @localtime_only($model->created_at)
         * Output: "18:30"
         */
        Blade::directive('localtime_only', function (string $expression) {
            return "<?php echo app(\App\Modules\Timezone\Services\TimezoneService::class)->format({$expression}, 'H:i'); ?>";
        });

        /**
         * @localscript
         * Injeksi script untuk deteksi timezone di sisi klien.
         */
        Blade::directive('localscript', function () {
            return "<script>
                const tz = Intl.DateTimeFormat().resolvedOptions().timeZone;
                if (tz && tz !== '" . app(TimezoneService::class)->getUserTimezone() . "') {
                    fetch('/api/v1/timezone/set', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({ timezone: tz })
                    });
                }
            </script>";
        });

        /**
         * @humanstime($model->created_at)
         * Output: "5 menit yang lalu"
         * Waktu relatif dalam timezone lokal user.
         */
        Blade::directive('humanstime', function (string $expression) {
            return "<?php echo app(\App\Modules\Timezone\Services\TimezoneService::class)->diffForHumans({$expression}); ?>";
        });

        /**
         * @timezone
         * Output: "Asia/Jakarta"
         * Tampilkan nama timezone aktif user.
         */
        Blade::directive('timezone', function () {
            return "<?php echo app(\App\Modules\Timezone\Services\TimezoneService::class)->getUserTimezone(); ?>";
        });
    }
}
