<?php

declare(strict_types=1);

namespace Svidskiy\Modulith\Commands;

use Illuminate\Console\Command;
use Svidskiy\Modulith\Contracts\ModuleRepository;

final class ListCommand extends Command
{
    protected $signature = 'module:list';

    protected $description = 'List all registered modules.';

    public function handle(ModuleRepository $repository): int
    {
        $modules = $repository->all();

        if ($modules === []) {
            $this->components->info('No modules registered.');

            return self::SUCCESS;
        }

        $rows = [];

        foreach ($modules as $module) {
            $rows[] = [$module->name, $module->namespace, $module->path];
        }

        $this->table(['Name', 'Namespace', 'Path'], $rows);

        return self::SUCCESS;
    }
}
