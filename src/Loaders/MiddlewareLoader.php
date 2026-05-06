<?php

declare(strict_types=1);

namespace Svidskiy\Modulith\Loaders;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Svidskiy\Modulith\Contracts\Loader;
use Svidskiy\Modulith\Module;

final readonly class MiddlewareLoader implements Loader
{
    public function __construct(
        private Application $app,
    ) {}

    public function load(Module $module): void
    {
        $file = sprintf('%s/middleware.php', $module->path);

        if (! is_file($file)) {
            return;
        }

        /** @var array{aliases?: array<string, class-string>, groups?: array<string, list<class-string|string>>} $config */
        $config = require $file;

        foreach ($config['aliases'] ?? [] as $name => $class) {
            Route::aliasMiddleware($name, $class);
        }

        foreach ($config['groups'] ?? [] as $name => $list) {
            Route::middlewareGroup($name, $list);
        }
    }
}
