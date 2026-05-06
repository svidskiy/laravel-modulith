<?php

declare(strict_types=1);

namespace Svidskiy\Modulith\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array<string, \Svidskiy\Modulith\Module> all()
 * @method static ?\Svidskiy\Modulith\Module find(string $name)
 * @method static \Svidskiy\Modulith\Module findOrFail(string $name)
 * @method static bool has(string $name)
 *
 * @see \Svidskiy\Modulith\Modulith
 */
final class Modulith extends Facade
{
    #[\Override]
    protected static function getFacadeAccessor(): string
    {
        return \Svidskiy\Modulith\Modulith::class;
    }
}
