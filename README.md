# Laravel Modulith

> Vertical Slice Architecture for Laravel — group your code by business feature, scale to hundreds of models without losing your mind.

> **Status:** v0.1 — early access. Public API may change before v1.0.

## Requirements

- PHP 8.3+
- Laravel 12+

## Installation

```bash
composer require svidskiy/laravel-modulith
php artisan modulith:install
```

## Create a module

```bash
php artisan module:make Billing
```

Scaffolds `modules/Billing/`:

```
modules/Billing/
├── Http/Controllers/
├── Models/
├── database/migrations/
└── routes/
```

Anything you drop inside is auto-loaded — no manual provider registration.

## Commands

| Command | Purpose |
| --- | --- |
| `module:make {name}` | Scaffold a new module |
| `module:list` | Show registered modules |
| `module:cache` | Cache the module manifest |
| `module:clear` | Drop the cache |
| `modulith:install` | Publish the config |

## Configuration

`config/modulith.php` after `modulith:install`:

```php
return [
    'path' => 'modules',          // base_path(...) prefix or absolute
    'namespace' => 'Modules',     // PSR-4 root for module classes
    'cache' => ['enabled' => env('MODULITH_CACHE', true)],
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
```

## What gets auto-loaded per module

| Path inside module | Loader |
| --- | --- |
| `routes/web.php`, `routes/api.php` | `RouteLoader` |
| `database/migrations/*.php` | `MigrationLoader` |
| `config/*.php` | `ConfigLoader` |
| `resources/views/` | `ViewLoader` (namespaced as `<module>::`) |
| `lang/` | `TranslationLoader` |
| `View/Components/` | `BladeComponentLoader` |
| `Console/*.php` | `CommandLoader` |
| `Listeners/*.php` | `EventLoader` |
| `Observers/*Observer.php` | `ObserverLoader` (auto-binds to matching `Models/*`) |
| `Policies/*Policy.php` | `PolicyLoader` (auto-binds to matching `Models/*`) |
| `middleware.php` | `MiddlewareLoader` |

## License

MIT
