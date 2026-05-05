<?php

declare(strict_types=1);

namespace Svidskiy\Modulith\Contracts;

use Svidskiy\Modulith\Exceptions\ModuleNotFoundException;
use Svidskiy\Modulith\Module;

interface ModuleRepository
{
    /**
     * @return array<string, Module>
     */
    public function all(): array;

    public function find(string $name): ?Module;

    /**
     * @throws ModuleNotFoundException
     */
    public function findOrFail(string $name): Module;

    public function has(string $name): bool;
}
