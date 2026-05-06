<?php

declare(strict_types=1);

namespace Svidskiy\Modulith\Loaders;

use Illuminate\Database\Migrations\Migrator;
use Illuminate\Foundation\Application;
use Svidskiy\Modulith\Contracts\Loader;
use Svidskiy\Modulith\Module;

final readonly class MigrationLoader implements Loader
{
    public function __construct(
        private Application $app,
    ) {}

    public function load(Module $module): void
    {
        $path = sprintf('%s/database/migrations', $module->path);

        $this->app->callAfterResolving('migrator', static function (Migrator $migrator) use ($path): void {
            $migrator->path($path);
        });
    }
}
