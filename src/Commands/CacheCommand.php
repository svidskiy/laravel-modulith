<?php

declare(strict_types=1);

namespace Svidskiy\Modulith\Commands;

use Illuminate\Console\Command;
use Svidskiy\Modulith\Contracts\ModuleCache;
use Svidskiy\Modulith\Contracts\ModuleRepository;

final class CacheCommand extends Command
{
    protected $signature = 'module:cache';

    protected $description = 'Cache the discovered modules manifest.';

    public function handle(ModuleCache $cache, ModuleRepository $repository): int
    {
        $cache->forget();

        $modules = $repository->all();

        $this->components->info(sprintf('Cached %d module(s).', count($modules)));

        return self::SUCCESS;
    }
}
