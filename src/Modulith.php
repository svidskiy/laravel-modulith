<?php

declare(strict_types=1);

namespace Svidskiy\Modulith;

use Illuminate\Container\Attributes\Singleton;
use Svidskiy\Modulith\Exceptions\ModuleNotFoundException;

#[Singleton]
final readonly class Modulith
{
    public function __construct(
        private ModuleRepository $repository,
    ) {}

    /**
     * @return array<string, Module>
     */
    public function all(): array
    {
        return $this->repository->all();
    }

    public function find(string $name): ?Module
    {
        return $this->repository->find($name);
    }

    /**
     * @throws ModuleNotFoundException
     */
    public function findOrFail(string $name): Module
    {
        return $this->repository->findOrFail($name);
    }

    public function has(string $name): bool
    {
        return $this->repository->has($name);
    }
}
