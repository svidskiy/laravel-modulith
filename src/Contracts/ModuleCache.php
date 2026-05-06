<?php

declare(strict_types=1);

namespace Svidskiy\Modulith\Contracts;

use Svidskiy\Modulith\Module;

/**
 * @phpstan-import-type ModuleArray from Module
 */
interface ModuleCache
{
    /**
     * @return ?array<string, ModuleArray>
     */
    public function get(): ?array;

    /**
     * @param  array<string, ModuleArray>  $modules
     */
    public function put(array $modules): void;

    public function forget(): void;

    public function has(): bool;
}
