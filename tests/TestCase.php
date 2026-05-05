<?php

declare(strict_types=1);

namespace Svidskiy\Modulith\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Svidskiy\Modulith\ModulithServiceProvider;

abstract class TestCase extends BaseTestCase
{
    #[\Override]
    protected function getPackageProviders($app): array
    {
        return [
            ModulithServiceProvider::class,
        ];
    }
}
