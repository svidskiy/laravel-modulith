<?php

declare(strict_types=1);

namespace Svidskiy\Modulith\Loaders;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Blade;
use Svidskiy\Modulith\Contracts\Loader;
use Svidskiy\Modulith\Module;

final readonly class BladeComponentLoader implements Loader
{
    public function __construct(
        private Application $app,
    ) {}

    public function load(Module $module): void
    {
        Blade::componentNamespace(
            sprintf('%s\\View\\Components', $module->namespace),
            strtolower($module->name),
        );
    }
}
