<?php

declare(strict_types=1);

namespace Svidskiy\Modulith\Loaders;

use Illuminate\Foundation\Application;
use Svidskiy\Modulith\Contracts\Loader;
use Svidskiy\Modulith\Module;

final readonly class ScheduleLoader implements Loader
{
    public function __construct(
        private Application $app,
    ) {}

    public function load(Module $module): void {}
}
