<?php

declare(strict_types=1);

namespace Svidskiy\Modulith;

use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;
use Override;
use Svidskiy\Modulith\Cache\FileModuleCache;
use Svidskiy\Modulith\Commands\CacheCommand;
use Svidskiy\Modulith\Commands\ClearCommand;
use Svidskiy\Modulith\Commands\InstallCommand;
use Svidskiy\Modulith\Commands\ListCommand;
use Svidskiy\Modulith\Commands\MakeCommand;
use Svidskiy\Modulith\Contracts\ModuleCache;
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
use Svidskiy\Modulith\Repositories\CachedModuleRepository;
use Svidskiy\Modulith\Repositories\FilesystemModuleRepository;

final class ModulithServiceProvider extends ServiceProvider
{
    /**
     * @var array<string, class-string<ModuleLoader>>
     */
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

    #[Override]
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/modulith.php', 'modulith');

        $this->registerCache();
        $this->registerRepository();
        $this->registerManager();
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/modulith.php' => config_path('modulith.php'),
            ], 'modulith-config');

            $this->commands([
                CacheCommand::class,
                ClearCommand::class,
                InstallCommand::class,
                ListCommand::class,
                MakeCommand::class,
            ]);

            $this->optimizes(
                optimize: 'module:cache',
                clear: 'module:clear',
            );
        }

        $this->bootModules();
    }

    private function registerCache(): void
    {
        $this->app->singleton(ModuleCache::class, static fn (Application $app): ModuleCache => new FileModuleCache(
            $app->make(Filesystem::class),
            $app->bootstrapPath('cache/modulith.php'),
        ));
    }

    private function registerRepository(): void
    {
        $this->app->singleton(static function (Application $app): ModuleRepository {
            $config = $app->make(ConfigRepository::class);

            $path = $config->string('modulith.path', 'modules');
            $absolutePath = str_starts_with($path, '/') ? $path : $app->basePath($path);

            $base = new FilesystemModuleRepository(
                $app->make(Filesystem::class),
                $absolutePath,
                $config->string('modulith.namespace', 'Modules'),
            );

            if (! $config->boolean('modulith.cache.enabled', true)) {
                return $base;
            }

            return new CachedModuleRepository($base, $app->make(ModuleCache::class));
        });
    }

    private function registerManager(): void
    {
        $this->app->singleton(Modulith::class, static fn (Application $app): Modulith => new Modulith(
            $app->make(ModuleRepository::class),
        ));
    }

    private function bootModules(): void
    {
        $repository = $this->app->make(ModuleRepository::class);
        $config = $this->app->make(ConfigRepository::class);

        foreach ($repository->all() as $module) {
            foreach (self::LOADERS as $key => $loaderClass) {
                if (! $config->boolean("modulith.auto_discover.$key", true)) {
                    continue;
                }

                $this->app->make($loaderClass)->load($module);
            }
        }
    }
}
