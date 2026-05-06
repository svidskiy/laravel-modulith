<?php

declare(strict_types=1);

namespace Svidskiy\Modulith\Loaders;

use Illuminate\Support\Facades\Blade;
use Override;
use Svidskiy\Modulith\Contracts\ModuleLoader;
use Svidskiy\Modulith\Module;

final readonly class BladeComponentLoader implements ModuleLoader
{
    #[Override]
    public function load(Module $module): void
    {
        Blade::componentNamespace(
            sprintf('%s\\View\\Components', $module->namespace),
            strtolower($module->name),
        );
    }
}
