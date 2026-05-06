<?php

declare(strict_types=1);

namespace Svidskiy\Modulith;

/**
 * @phpstan-type ModuleArray array{name: string, path: string, namespace: string}
 */
final readonly class Module
{
    public function __construct(
        public string $name,
        public string $path,
        public string $namespace,
    ) {}

    /**
     * @return ModuleArray
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'path' => $this->path,
            'namespace' => $this->namespace,
        ];
    }

    /**
     * @param  ModuleArray  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            path: $data['path'],
            namespace: $data['namespace'],
        );
    }
}
