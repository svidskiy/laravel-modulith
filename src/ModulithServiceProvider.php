<?php

declare(strict_types=1);

namespace Svidskiy\Modulith;

use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\ServiceProvider;
use Override;
use Svidskiy\Modulith\Commands\CacheCommand;
use Svidskiy\Modulith\Commands\ClearCommand;
use Svidskiy\Modulith\Commands\InstallCommand;
use Svidskiy\Modulith\Commands\ListCommand;
use Svidskiy\Modulith\Commands\MakeCommand;
use Svidskiy\Modulith\Contracts\ModuleLoader;
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
    public const string LOADERS_TAG = 'modulith.loaders';

    /** @var list<class-string<ModuleLoader>> */
    private const array LOADERS = [
        ConfigLoader::class,
        RouteLoader::class,
        ViewLoader::class,
        TranslationLoader::class,
        MigrationLoader::class,
        CommandLoader::class,
        BladeComponentLoader::class,
        PolicyLoader::class,
        EventLoader::class,
        ObserverLoader::class,
        MiddlewareLoader::class,
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

        $this->app->tag(self::LOADERS, self::LOADERS_TAG);
    }

    /**
     * @throws BindingResolutionException
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                $this->configPath() => config_path('modulith.php'),
            ], 'modulith-config');

            $this->commands(self::COMMANDS);

            $this->optimizes(optimize: 'module:cache', clear: 'module:clear');
        }

        $this->loadModules();
    }

    /**
     * @throws BindingResolutionException
     */
    private function loadModules(): void
    {
        $modules = $this->app->make(ModuleRepository::class)->all();

        /** @var iterable<ModuleLoader> $loaders */
        $loaders = $this->app->tagged(self::LOADERS_TAG);

        foreach ($loaders as $loader) {
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
