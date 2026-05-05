<?php

declare(strict_types=1);

return [
    'path' => 'modules',

    'namespace' => 'Modules',

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

    'default_folders' => [
        'Models',
        'Http/Controllers',
        'Database/Migrations',
        'Database/Factories',
        'Database/Seeders',
        'routes',
        'resources/views',
        'tests/Unit',
        'tests/Feature',
    ],
];
