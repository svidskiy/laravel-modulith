<?php

declare(strict_types=1);

namespace Svidskiy\Modulith\Commands;

use Illuminate\Console\Command;

final class CacheCommand extends Command
{
    protected $signature = 'module:cache';

    protected $description = 'Cache the discovered modules manifest.';

    public function handle(): int
    {
        // TODO: implement

        return self::SUCCESS;
    }
}
