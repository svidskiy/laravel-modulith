<?php

declare(strict_types=1);

namespace Svidskiy\Modulith\Commands;

use Illuminate\Console\Command;
use Svidskiy\Modulith\Contracts\ModuleCache;

final class ClearCommand extends Command
{
    protected $signature = 'modulith:clear';

    protected $description = 'Remove the modules manifest cache.';

    public function handle(ModuleCache $cache): int
    {
        $cache->forget();

        $this->components->info('Module cache cleared.');

        return self::SUCCESS;
    }
}
