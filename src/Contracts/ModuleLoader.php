<?php

declare(strict_types=1);

namespace Svidskiy\Modulith\Contracts;

use Svidskiy\Modulith\Module;

interface ModuleLoader
{
    public function load(Module $module): void;
}
