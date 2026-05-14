<?php

declare(strict_types=1);

namespace Svidskiy\Modulith\Commands;

use Illuminate\Console\Command;

final class MakeCommand extends Command
{
    protected $signature = 'make:module
                            {name : The module name in StudlyCase}
                            {--force : Overwrite an existing module}';

    protected $description = 'Scaffold a new module.';

    public function handle(): int
    {
        // TODO: implement

        return self::SUCCESS;
    }
}
