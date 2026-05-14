<?php

declare(strict_types=1);

namespace Svidskiy\Modulith\Commands;

use Illuminate\Console\Command;

final class ClearCommand extends Command
{
    protected $signature = 'module:clear';

    protected $description = 'Remove the modules manifest cache.';

    public function handle(): int
    {
        // TODO: implement

        return self::SUCCESS;
    }
}
