<?php

declare(strict_types=1);

namespace Svidskiy\Modulith\Facades;

use Illuminate\Support\Facades\Facade;
use Override;
use Svidskiy\Modulith\Module;

/**
 * @method static array<string, Module> all()
 * @method static ?Module find(string $name)
 * @method static Module findOrFail(string $name)
 * @method static bool has(string $name)
 *
 * @see \Svidskiy\Modulith\Modulith
 */
final class Modulith extends Facade
{
    #[Override]
    protected static function getFacadeAccessor(): string
    {
        return \Svidskiy\Modulith\Modulith::class;
    }
}
