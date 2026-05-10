<?php

declare(strict_types=1);

namespace Svidskiy\Modulith\Commands;

use Illuminate\Console\Command;
use Svidskiy\Modulith\Contracts\ModuleCache;
use Svidskiy\Modulith\Contracts\ModuleRepository;
use Svidskiy\Modulith\Module;

final class CacheCommand extends Command
{
    protected $signature = 'modulith:cache';

    protected $description = 'Cache the discovered modules manifest.';

    public function handle(ModuleCache $cache, ModuleRepository $repository): int
    {
        $cache->forget();

        $modules = $repository->all();

        $cache->put(array_map(
            static fn (Module $module): array => $module->toArray(),
            $modules,
        ));

        $this->components->info(sprintf('Cached %d module(s).', count($modules)));

        return self::SUCCESS;
    }
}
