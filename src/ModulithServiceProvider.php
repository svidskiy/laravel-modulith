<?php

declare(strict_types=1);

namespace Svidskiy\Modulith;

use Illuminate\Support\ServiceProvider;

final class ModulithServiceProvider extends ServiceProvider
{
    #[\Override]
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/modulith.php', 'modulith');

        $this->app->singleton(Modulith::class);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/modulith.php' => config_path('modulith.php'),
            ], 'modulith-config');
        }
    }
}
