<?php

declare(strict_types=1);

namespace Svidskiy\Modulith\Loaders;

use Illuminate\Foundation\Application;
use Illuminate\Translation\Translator;
use Svidskiy\Modulith\Contracts\ModuleLoader;
use Svidskiy\Modulith\Module;

final readonly class TranslationLoader implements ModuleLoader
{
    public function __construct(
        private Application $app,
    ) {}

    #[\Override]
    public function load(Module $module): void
    {
        $namespace = strtolower($module->name);
        $path = sprintf('%s/lang', $module->path);

        $this->app->afterResolving('translator', static function (Translator $translator) use ($namespace, $path): void {
            $translator->addNamespace($namespace, $path);
            $translator->addJsonPath($path);
        });
    }
}
