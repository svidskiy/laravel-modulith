<?php

declare(strict_types=1);

return [
    'path' => 'modules',

    'namespace' => 'Modules',

    'cache' => [
        'enabled' => env('MODULITH_CACHE', true),
    ],

    'auto_discover' => [
        'config' => true,
        'routes' => true,
        'views' => true,
        'translations' => true,
        'migrations' => true,
        'commands' => true,
        'blade_components' => true,
        'policies' => true,
        'events' => true,
        'observers' => true,
        'middleware' => true,
    ],
];
