<?php

declare(strict_types=1);

namespace Svidskiy\Modulith\Loaders;

use Illuminate\Support\Facades\Event;
use Svidskiy\Modulith\Contracts\ModuleLoader;
use Svidskiy\Modulith\Module;

final readonly class EventLoader implements ModuleLoader
{
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
