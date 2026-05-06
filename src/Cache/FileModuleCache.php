<?php

declare(strict_types=1);

namespace Svidskiy\Modulith\Cache;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Override;
use Svidskiy\Modulith\Contracts\ModuleCache;
use Svidskiy\Modulith\Module;

/**
 * @phpstan-import-type ModuleArray from Module
 */
final readonly class FileModuleCache implements ModuleCache
{
    public function __construct(
        private Filesystem $files,
        private string $path,
    ) {}

    /**
     * @return ?array<string, ModuleArray>
     */
    #[Override]
    public function get(): ?array
    {
        if (! $this->files->isFile($this->path)) {
            return null;
        }

        try {
            $data = $this->files->getRequire($this->path);
        } catch (FileNotFoundException) {
            return null;
        }

        if (! is_array($data)) {
            return null;
        }

        /** @var array<string, ModuleArray> $data */
        return $data;
    }

    /**
     * @param  array<string, ModuleArray>  $modules
     */
    #[Override]
    public function put(array $modules): void
    {
        $this->files->ensureDirectoryExists(dirname($this->path));

        $this->files->replace(
            $this->path,
            sprintf('<?php return %s;%s', var_export($modules, true), PHP_EOL),
        );

        $this->invalidateOpcache();
    }

    #[Override]
    public function forget(): void
    {
        $this->files->delete($this->path);

        $this->invalidateOpcache();
    }

    #[Override]
    public function has(): bool
    {
        return $this->files->isFile($this->path);
    }

    private function invalidateOpcache(): void
    {
        if (function_exists('opcache_invalidate')) {
            @opcache_invalidate($this->path, true);
        }
    }
}
