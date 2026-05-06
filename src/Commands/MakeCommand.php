<?php

declare(strict_types=1);

namespace Svidskiy\Modulith\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Filesystem\Filesystem;

final class MakeCommand extends Command
{
    protected $signature = 'module:make
                            {name : The module name in StudlyCase}
                            {--force : Overwrite an existing module}';

    protected $description = 'Scaffold a new module.';

    /**
     * @var list<string>
     */
    private const array FOLDERS = [
        'Database/Migrations',
        'Http/Controllers',
        'Models',
        'routes',
    ];

    public function handle(Filesystem $files, Repository $config): int
    {
        $name = $this->argument('name');

        if (! is_string($name) || preg_match('/^[A-Z][A-Za-z0-9]*$/', $name) !== 1) {
            $this->components->error('Module name must be StudlyCase.');

            return self::FAILURE;
        }

        $base = base_path($config->string('modulith.path', 'modules')).'/'.$name;

        if ($files->isDirectory($base) && $this->option('force') !== true) {
            $this->components->error(sprintf('Module [%s] already exists at %s.', $name, $base));

            return self::FAILURE;
        }

        foreach (self::FOLDERS as $folder) {
            $files->ensureDirectoryExists($base.'/'.$folder);
        }

        $this->components->info(sprintf('Module [%s] scaffolded at %s.', $name, $base));

        return self::SUCCESS;
    }
}
