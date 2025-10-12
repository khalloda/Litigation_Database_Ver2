<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

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
        // Fix for MySQL utf8mb4 index length issue
        Schema::defaultStringLength(191);

        // Universal timestamp formatter directive
        Blade::directive('formatTimestamp', function ($expression) {
            return "<?php 
                \$value = $expression;
                if (\$value && is_object(\$value) && method_exists(\$value, 'format')) {
                    echo \$value->format('Y-m-d H:i');
                } else {
                    echo __('app.not_set');
                }
            ?>";
        });
    }
}
