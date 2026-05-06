<?php

declare(strict_types=1);

namespace Svidskiy\Modulith\Loaders;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Application;
use Svidskiy\Modulith\Contracts\Loader;
use Svidskiy\Modulith\Module;

final readonly class ConfigLoader implements Loader
{
    public function __construct(
        private Application $app,
    ) {}

    /**
     * @throws BindingResolutionException
     */
    public function load(Module $module): void
    {
        if ($this->app->configurationIsCached()) {
            return;
        }

        /** @var Repository $config */
        $config = $this->app->make('config');

        foreach (glob(sprintf('%s/config/*.php', $module->path)) ?: [] as $path) {
            $key = basename($path, '.php');

            $config->set($key, array_merge(require $path, $config->get($key, [])));
        }
    }
}
