<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Svidskiy\Modulith\Facades\Modulith;
use Svidskiy\Modulith\Module;

Route::get('/', static fn () => [
    'package' => 'svidskiy/laravel-modulith',
    'modules' => array_map(
        static fn (Module $module): array => $module->toArray(),
        Modulith::all(),
    ),
]);
