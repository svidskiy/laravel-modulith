<?php

declare(strict_types=1);

namespace Svidskiy\Modulith\Loaders;

use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Application;
use Svidskiy\Modulith\Contracts\ModuleLoader;
use Svidskiy\Modulith\Module;

final readonly class ViewLoader implements ModuleLoader
{
    public function __construct(
        private Application $app,
    ) {}

    #[\Override]
    public function load(Module $module): void
    {
        $namespace = strtolower($module->name);
        $path = sprintf('%s/resources/views', $module->path);

        $this->app->callAfterResolving('view', static function (Factory $view) use ($namespace, $path): void {
            $view->addNamespace($namespace, $path);
        });
    }
}
