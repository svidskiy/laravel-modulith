<?php

declare(strict_types=1);

namespace Svidskiy\Modulith\Loaders;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Svidskiy\Modulith\Contracts\ModuleLoader;
use Svidskiy\Modulith\Module;

final readonly class RouteLoader implements ModuleLoader
{
    public function __construct(
        private Application $app,
    ) {}

    #[\Override]
    public function load(Module $module): void
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        $web = sprintf('%s/routes/web.php', $module->path);
        $api = sprintf('%s/routes/api.php', $module->path);

        if (is_file($web)) {
            Route::middleware('web')->group($web);
        }

        if (is_file($api)) {
            Route::middleware('api')->prefix('api')->group($api);
        }
    }
}
