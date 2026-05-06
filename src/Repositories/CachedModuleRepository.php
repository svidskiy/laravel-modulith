<?php

declare(strict_types=1);

namespace Svidskiy\Modulith\Repositories;

use Override;
use Svidskiy\Modulith\Contracts\ModuleCache;
use Svidskiy\Modulith\Contracts\ModuleRepository;
use Svidskiy\Modulith\Exceptions\ModuleNotFoundException;
use Svidskiy\Modulith\Module;

/**
 * @phpstan-import-type ModuleArray from Module
 */
final class CachedModuleRepository implements ModuleRepository
{
    /**
     * @var ?array<string, Module>
     */
    private ?array $modules = null;

    public function __construct(
        private readonly ModuleRepository $inner,
        private readonly ModuleCache $cache,
    ) {}

    /**
     * @return array<string, Module>
     */
    #[Override]
    public function all(): array
    {
        if ($this->modules !== null) {
            return $this->modules;
        }

        $cached = $this->cache->get();

        if ($cached !== null) {
            return $this->modules = $this->hydrate($cached);
        }

        $fresh = $this->inner->all();
        $this->cache->put($this->serialize($fresh));

        return $this->modules = $fresh;
    }

    #[Override]
    public function find(string $name): ?Module
    {
        return $this->all()[$name] ?? null;
    }

    /**
     * @throws ModuleNotFoundException
     */
    #[Override]
    public function findOrFail(string $name): Module
    {
        return $this->find($name) ?? throw ModuleNotFoundException::forName($name);
    }

    #[Override]
    public function has(string $name): bool
    {
        return isset($this->all()[$name]);
    }

    /**
     * @param  array<string, ModuleArray>  $data
     * @return array<string, Module>
     */
    private function hydrate(array $data): array
    {
        return array_map(
            Module::fromArray(...),
            $data,
        );
    }

    /**
     * @param  array<string, Module>  $modules
     * @return array<string, ModuleArray>
     */
    private function serialize(array $modules): array
    {
        return array_map(
            static fn (Module $module): array => $module->toArray(),
            $modules,
        );
    }
}
