<?php

declare(strict_types=1);

namespace Svidskiy\Modulith;

final readonly class Module
{
    public function __construct(
        public string $name,
        public string $path,
        public string $namespace,
    ) {}

    /**
     * @return array{name: string, path: string, namespace: string}
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
     * @param  array{name: string, path: string, namespace: string}  $data
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
