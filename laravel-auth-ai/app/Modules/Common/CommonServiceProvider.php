<?php

namespace App\Modules\Common;

use App\Modules\Common\Services\FormattingService;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class CommonServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(FormattingService::class);
    }

    public function boot(): void
    {
        $this->registerBladeDirectives();
        $this->registerGlobalHelpers();
    }

    private function registerBladeDirectives(): void
    {
        /**
         * @shortnum(1500) -> 1.5K
         */
        Blade::directive('shortnum', function ($expression) {
            return "<?php echo app(\App\Modules\Common\Services\FormattingService::class)->shortNumber($expression); ?>";
        });

        /**
         * @money(50000) -> Rp 50.000
         */
        Blade::directive('money', function ($expression) {
            return "<?php echo app(\App\Modules\Common\Services\FormattingService::class)->formatMoney($expression); ?>";
        });

        /**
         * @bytes(1048576) -> 1 MB
         */
        Blade::directive('bytes', function ($expression) {
            return "<?php echo app(\App\Modules\Common\Services\FormattingService::class)->formatBytes($expression); ?>";
        });

        /**
         * @percent(0.85) -> 85%
         */
        Blade::directive('percent', function ($expression) {
            return "<?php echo app(\App\Modules\Common\Services\FormattingService::class)->formatPercent($expression); ?>";
        });
    }

    private function registerGlobalHelpers(): void
    {
        // Global helpers are usually better in a separate file loaded via composer.json
        // for better performance and consistency.
    }
}
