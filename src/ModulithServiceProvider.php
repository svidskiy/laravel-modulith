<?php

declare(strict_types=1);

namespace Svidskiy\Modulith;

use Illuminate\Console\Command;
use Illuminate\Support\ServiceProvider;
use Override;
use Svidskiy\Modulith\Commands\CacheCommand;
use Svidskiy\Modulith\Commands\ClearCommand;
use Svidskiy\Modulith\Commands\InstallCommand;
use Svidskiy\Modulith\Commands\ListCommand;
use Svidskiy\Modulith\Commands\MakeCommand;
use Svidskiy\Modulith\Contracts\ModuleLoader;
use Svidskiy\Modulith\Contracts\ModuleRepository;
use Svidskiy\Modulith\Loaders\BladeComponentLoader;
use Svidskiy\Modulith\Loaders\CommandLoader;
use Svidskiy\Modulith\Loaders\ConfigLoader;
use Svidskiy\Modulith\Loaders\EventLoader;
use Svidskiy\Modulith\Loaders\MiddlewareLoader;
use Svidskiy\Modulith\Loaders\MigrationLoader;
use Svidskiy\Modulith\Loaders\ObserverLoader;
use Svidskiy\Modulith\Loaders\PolicyLoader;
use Svidskiy\Modulith\Loaders\RouteLoader;
use Svidskiy\Modulith\Loaders\TranslationLoader;
use Svidskiy\Modulith\Loaders\ViewLoader;

final class ModulithServiceProvider extends ServiceProvider
{
    /** @var array<string, class-string<ModuleLoader>> */
    private const array LOADERS = [
        'config' => ConfigLoader::class,
        'routes' => RouteLoader::class,
        'views' => ViewLoader::class,
        'translations' => TranslationLoader::class,
        'migrations' => MigrationLoader::class,
        'commands' => CommandLoader::class,
        'blade_components' => BladeComponentLoader::class,
        'policies' => PolicyLoader::class,
        'events' => EventLoader::class,
        'observers' => ObserverLoader::class,
        'middleware' => MiddlewareLoader::class,
    ];

    /** @var list<class-string<Command>> */
    private const array COMMANDS = [
        CacheCommand::class,
        ClearCommand::class,
        InstallCommand::class,
        ListCommand::class,
        MakeCommand::class,
    ];

    #[Override]
    public function register(): void
    {
        $this->mergeConfigFrom($this->configPath(), 'modulith');

        $this->app->singleton(Modulith::class);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                $this->configPath() => config_path('modulith.php'),
            ], 'modulith-config');

            $this->commands(self::COMMANDS);

            $this->optimizes(optimize: 'modulith:cache', clear: 'modulith:clear');
        }

        $this->loadModules();
    }

    private function loadModules(): void
    {
        $modules = app(ModuleRepository::class)->all();

        foreach (self::LOADERS as $key => $class) {
            if (! config("modulith.auto_discover.$key", true)) {
                continue;
            }

            $loader = app($class);

            foreach ($modules as $module) {
                $loader->load($module);
            }
        }
    }

    private function configPath(): string
    {
        return sprintf('%s/../config/modulith.php', __DIR__);
    }
}
