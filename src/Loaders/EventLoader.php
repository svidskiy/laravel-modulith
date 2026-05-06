<?php

declare(strict_types=1);

namespace Svidskiy\Modulith\Loaders;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Event;
use Svidskiy\Modulith\Contracts\ModuleLoader;
use Svidskiy\Modulith\Module;

final readonly class EventLoader implements ModuleLoader
{
    public function __construct(
        private Application $app,
    ) {}

    #[\Override]
    public function load(Module $module): void
    {
        foreach (glob(sprintf('%s/Listeners/*.php', $module->path)) ?: [] as $file) {
            $listener = sprintf('%s\\Listeners\\%s', $module->namespace, basename($file, '.php'));

            if (class_exists($listener)) {
                Event::listen($listener);
            }
        }
    }
}
