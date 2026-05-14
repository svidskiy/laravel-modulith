<?php

declare(strict_types=1);

namespace Svidskiy\Modulith\Commands;

use Illuminate\Console\Command;

final class ListCommand extends Command
{
    protected $signature = 'module:list';

    protected $description = 'List all registered modules.';

    public function handle(): int
    {
        // TODO: implement

        return self::SUCCESS;
    }
}
