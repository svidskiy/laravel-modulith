<?php

declare(strict_types=1);

namespace Svidskiy\Modulith\Loaders;

use Illuminate\Container\Attributes\Config;
use Illuminate\Container\Attributes\Singleton;
use Override;
use Svidskiy\Modulith\Contracts\ModuleLoader;
use Svidskiy\Modulith\Module;

#[Singleton]
final readonly class ObserverLoader implements ModuleLoader
{
    public function __construct(
        #[Config('modulith.auto_discover.observers', true)] private bool $enabled,
    ) {}

    #[Override]
    public function load(Module $module): void
    {
        if (! $this->enabled) {
            return;
        }
        //
    }
}
