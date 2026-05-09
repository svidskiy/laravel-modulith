<?php

declare(strict_types=1);

namespace Svidskiy\Modulith\Loaders;

use Override;
use Svidskiy\Modulith\Contracts\ModuleLoader;
use Svidskiy\Modulith\Module;

final readonly class ConfigLoader implements ModuleLoader
{
    #[Override]
    public function load(Module $module): void
    {
        //
    }
}
