<?php

declare(strict_types=1);

namespace Svidskiy\Modulith;

use Illuminate\Container\Attributes\Config;
use Illuminate\Container\Attributes\Singleton;
use Illuminate\Filesystem\Filesystem;
use Svidskiy\Modulith\Cache\FileModuleCache;
use Svidskiy\Modulith\Exceptions\InvalidModuleException;
use Svidskiy\Modulith\Exceptions\ModuleNotFoundException;

#[Singleton]
final class ModuleRepository
{
    /** @var ?array<string, Module> */
    private ?array $modules = null;

    private readonly string $modulesPath;

    public function __construct(
        private readonly Filesystem $files,
        private readonly FileModuleCache $cache,
        #[Config('modulith.path', 'modules')] string $path,
        #[Config('modulith.namespace', 'Modules')] private readonly string $namespacePrefix,
        #[Config('modulith.cache.enabled', true)] private readonly bool $useCache,
    ) {
        $this->modulesPath = base_path($path);
    }

    /**
     * @return array<string, Module>
     */
    public function all(): array
    {
        if ($this->modules !== null) {
            return $this->modules;
        }

        if ($this->useCache && ($cached = $this->cache->get()) !== null) {
            return $this->modules = array_map(Module::fromArray(...), $cached);
        }

        $modules = $this->scan();

        if ($this->useCache) {
            $this->cache->put(array_map(static fn (Module $m): array => $m->toArray(), $modules));
        }

        return $this->modules = $modules;
    }

    public function find(string $name): ?Module
    {
        return $this->all()[$name] ?? null;
    }

    /**
     * @throws ModuleNotFoundException
     */
    public function findOrFail(string $name): Module
    {
        return $this->find($name) ?? throw ModuleNotFoundException::forName($name);
    }

    public function has(string $name): bool
    {
        return isset($this->all()[$name]);
    }

    /**
     * @return array<string, Module>
     */
    private function scan(): array
    {
        if (! $this->files->isDirectory($this->modulesPath)) {
            return [];
        }

        $modules = [];

        foreach ($this->files->directories($this->modulesPath) as $directory) {
            if (! is_string($directory)) {
                continue;
            }

            $name = basename($directory);

            if (preg_match('/^[A-Z][A-Za-z0-9]*$/', $name) !== 1) {
                throw InvalidModuleException::forPath(
                    $directory,
                    sprintf('module folder [%s] must be StudlyCase', $name),
                );
            }

            $modules[$name] = new Module(
                name: $name,
                path: $directory,
                namespace: sprintf('%s\\%s', $this->namespacePrefix, $name),
            );
        }

        ksort($modules);

        return $modules;
    }
}
