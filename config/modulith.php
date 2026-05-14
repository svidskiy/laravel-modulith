<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Modules Path
    |--------------------------------------------------------------------------
    |
    | The directory where your modules live, relative to the application
    | base path. Each immediate subdirectory is treated as a single
    | module that Modulith discovers automatically.
    |
    | Provide an absolute path to keep modules outside the project root.
    |
    */

    'path' => 'modules',

    /*
    |--------------------------------------------------------------------------
    | Modules Namespace
    |--------------------------------------------------------------------------
    |
    | The PSR-4 root namespace under which your module classes resolve.
    | A module at modules/Billing is loaded under the Modules\Billing
    | namespace, following the path-to-namespace convention.
    |
    | Register this namespace in your composer.json autoload section
    | so classes inside modules can be resolved by the framework.
    |
    */

    'namespace' => 'Modules',

    /*
    |--------------------------------------------------------------------------
    | Manifest Cache
    |--------------------------------------------------------------------------
    |
    | When enabled, the list of discovered modules is cached on disk
    | so the filesystem is not scanned on every request. Refresh
    | the cache after adding or removing modules in production.
    |
    | Disable this in local development to pick up new modules
    | without running a console command after every change.
    |
    */

    'cache' => [
        'enabled' => env('MODULITH_CACHE', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Auto-Discovery
    |--------------------------------------------------------------------------
    |
    | Each flag enables auto-loading of one convention across every
    | module. Disable a flag to wire that concern manually, or to
    | opt out of the convention entirely.
    |
    | See the Modulith documentation for the paths and naming rules
    | that each loader expects inside a module.
    |
    */

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
