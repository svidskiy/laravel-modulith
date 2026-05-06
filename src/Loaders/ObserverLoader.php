<?php

declare(strict_types=1);

namespace Svidskiy\Modulith\Loaders;

use Illuminate\Database\Eloquent\Model;
use Svidskiy\Modulith\Contracts\ModuleLoader;
use Svidskiy\Modulith\Module;

final readonly class ObserverLoader implements ModuleLoader
{
    #[\Override]
    public function load(Module $module): void
    {
        foreach (glob(sprintf('%s/Observers/*Observer.php', $module->path)) ?: [] as $file) {
            $name = basename($file, 'Observer.php');
            $model = sprintf('%s\\Models\\%s', $module->namespace, $name);
            $observer = sprintf('%s\\Observers\\%sObserver', $module->namespace, $name);

            if (! class_exists($model) || ! class_exists($observer)) {
                continue;
            }

            if (! is_subclass_of($model, Model::class)) {
                continue;
            }

            $model::observe($observer);
        }
    }
}
