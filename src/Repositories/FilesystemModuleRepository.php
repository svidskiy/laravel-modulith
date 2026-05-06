<?php

declare(strict_types=1);

namespace Svidskiy\Modulith\Repositories;

use Illuminate\Filesystem\Filesystem;
use Override;
use Svidskiy\Modulith\Contracts\ModuleRepository;
use Svidskiy\Modulith\Exceptions\InvalidModuleException;
use Svidskiy\Modulith\Exceptions\ModuleNotFoundException;
use Svidskiy\Modulith\Module;

final class FilesystemModuleRepository implements ModuleRepository
{
    /**
     * @var ?array<string, Module>
     */
    private ?array $modules = null;

    public function __construct(
        private readonly Filesystem $files,
        private readonly string $modulesPath,
        private readonly string $namespacePrefix,
    ) {}

    /**
     * @return array<string, Module>
     */
    #[Override]
    public function all(): array
    {
        return $this->modules ??= $this->scan();
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
