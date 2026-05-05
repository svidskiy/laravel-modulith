<?php

declare(strict_types=1);

use Svidskiy\Modulith\ModulithServiceProvider;

it('registers the service provider', function (): void {
    expect(app()->getProviders(ModulithServiceProvider::class))->not->toBeEmpty();
});
