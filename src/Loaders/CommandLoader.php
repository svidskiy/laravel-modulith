<?php

declare(strict_types=1);

namespace Svidskiy\Modulith\Loaders;

use Illuminate\Console\Application as ConsoleApplication;
use Illuminate\Console\Command;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Artisan;
use ReflectionClass;
use Svidskiy\Modulith\Contracts\ModuleLoader;
use Svidskiy\Modulith\Module;
use Symfony\Component\Finder\Finder;

final readonly class CommandLoader implements ModuleLoader
{
    public function __construct(
        private Application $app,
    ) {}

    #[\Override]
    public function load(Module $module): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $directory = sprintf('%s/Console', $module->path);

        if (! is_dir($directory)) {
            return;
        }

        foreach (Finder::create()->files()->in($directory)->name('*.php') as $file) {
            $relative = str_replace(['/', '.php'], ['\\', ''], $file->getRelativePathname());
            $class = sprintf('%s\\Console\\%s', $module->namespace, $relative);

            if (! is_subclass_of($class, Command::class)) {
                continue;
            }

            if ((new ReflectionClass($class))->isAbstract()) {
                continue;
            }

            Artisan::starting(static fn (ConsoleApplication $artisan) => $artisan->resolve($class));
        }
    }
}
