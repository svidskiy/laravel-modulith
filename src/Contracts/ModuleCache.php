<?php

declare(strict_types=1);

namespace Svidskiy\Modulith\Contracts;

interface ModuleCache
{
    /**
     * @return ?array<string, array<string, mixed>>
     */
    public function get(): ?array;

    /**
     * @param  array<string, array<string, mixed>>  $modules
     */
    public function put(array $modules): void;

    public function forget(): void;

    public function has(): bool;
}
