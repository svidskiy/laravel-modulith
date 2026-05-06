<?php

declare(strict_types=1);

namespace Svidskiy\Modulith\Loaders;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Application;
use Override;
use Svidskiy\Modulith\Contracts\ModuleLoader;
use Svidskiy\Modulith\Module;

final readonly class ConfigLoader implements ModuleLoader
{
    public function __construct(
        private Application $app,
    ) {}

    /**
     * @throws BindingResolutionException
     */
    #[Override]
    public function load(Module $module): void
    {
        if ($this->app->configurationIsCached()) {
            return;
        }

        /** @var Repository $config */
        $config = $this->app->make('config');

        foreach (glob(sprintf('%s/config/*.php', $module->path)) ?: [] as $path) {
            $key = basename($path, '.php');

            $loaded = require $path;
            $existing = $config->get($key, []);
            if (! is_array($loaded)) {
                continue;
            }
            if (! is_array($existing)) {
                continue;
            }

            $config->set($key, array_merge($loaded, $existing));
        }
    }
}
