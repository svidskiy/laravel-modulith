<?php

declare(strict_types=1);

namespace Svidskiy\Modulith\Cache;

use Illuminate\Container\Attributes\Singleton;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\Filesystem;
use Svidskiy\Modulith\Module;

/**
 * @phpstan-import-type ModuleArray from Module
 */
#[Singleton]
final readonly class FileModuleCache
{
    private string $path;

    public function __construct(
        private Filesystem $files,
        Application $app,
    ) {
        $this->path = $app->bootstrapPath('cache/modulith.php');
    }

    /**
     * @return ?array<string, ModuleArray>
     */
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
    public function put(array $modules): void
    {
        $this->files->ensureDirectoryExists(dirname($this->path));

        $this->files->replace(
            $this->path,
            sprintf('<?php return %s;%s', var_export($modules, true), PHP_EOL),
        );

        $this->invalidateOpcache();
    }

    public function forget(): void
    {
        $this->files->delete($this->path);

        $this->invalidateOpcache();
    }

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
