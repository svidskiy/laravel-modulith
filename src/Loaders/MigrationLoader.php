<?php

declare(strict_types=1);

namespace Svidskiy\Modulith\Loaders;

use Illuminate\Database\Migrations\Migrator;
use Illuminate\Foundation\Application;
use Override;
use Svidskiy\Modulith\Contracts\ModuleLoader;
use Svidskiy\Modulith\Module;

final readonly class MigrationLoader implements ModuleLoader
{
    public function __construct(
        private Application $app,
    ) {}

    #[Override]
    public function load(Module $module): void
    {
        $path = sprintf('%s/database/migrations', $module->path);

        $this->app->afterResolving('migrator', static function (Migrator $migrator) use ($path): void {
            $migrator->path($path);
        });
    }
}
