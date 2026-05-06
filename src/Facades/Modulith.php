<?php

declare(strict_types=1);

namespace Svidskiy\Modulith\Facades;

use Illuminate\Support\Facades\Facade;

/**
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
