<?php

declare(strict_types=1);

namespace Svidskiy\Modulith\Loaders;

use Illuminate\Support\Facades\Gate;
use Svidskiy\Modulith\Contracts\ModuleLoader;
use Svidskiy\Modulith\Module;

final readonly class PolicyLoader implements ModuleLoader
{
    #[\Override]
    public function load(Module $module): void
    {
        foreach (glob(sprintf('%s/Policies/*Policy.php', $module->path)) ?: [] as $file) {
            $name = basename($file, 'Policy.php');
            $model = sprintf('%s\\Models\\%s', $module->namespace, $name);
            $policy = sprintf('%s\\Policies\\%sPolicy', $module->namespace, $name);

            if (class_exists($model) && class_exists($policy)) {
                Gate::policy($model, $policy);
            }
        }
    }
}
