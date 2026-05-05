# PHPDoc reference

Concrete shapes for PHP 8.3 + Laravel 12 + PHPStan/Larastan.

## Generic repository

```php
<?php

declare(strict_types=1);

namespace App\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * @template TModel of Model
 */
abstract class Repository
{
    /**
     * @param  class-string<TModel>  $class
     */
    public function __construct(private string $class) {}

    /**
     * @return Collection<int, TModel>
     */
    public function all(): Collection
    {
        return $this->class::all();
    }

    /**
     * @return TModel
     *
     * @throws ModelNotFoundException
     */
    public function findOrFail(int $id): Model
    {
        return $this->class::findOrFail($id);
    }
}
```

## Eloquent model — auto-generated

Run once, then commit the result:

```bash
vendor/bin/testbench ide-helper:models -RW   # in a package
php artisan ide-helper:models -RW            # in an app
```

The output looks like this — never hand-write it:

```php
/**
 * @property int $id
 * @property string $email
 * @property string $name
 * @property ?\Illuminate\Support\Carbon $email_verified_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Order> $orders
 */
final class User extends Model
{
    /**
     * @return HasMany<Order, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
```

## Facade — auto-generated into _ide_helper.php

```bash
vendor/bin/testbench ide-helper:generate
```

The facade class itself stays minimal:

```php
<?php

declare(strict_types=1);

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \App\Manager
 */
final class Manager extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \App\Manager::class;
    }
}
```

Hand-write `@method` blocks only when intentionally curating a public subset.

## Sharing array shapes between files

Define once with `@phpstan-type`, import elsewhere with `@phpstan-import-type`:

```php
/**
 * @phpstan-type UserShape array{id: int, email: string, name: string}
 */
final class User extends Model {}
```

```php
use App\Models\User;

/**
 * @phpstan-import-type UserShape from User
 */
final class UserPresenter
{
    /**
     * @param  UserShape  $user
     */
    public function present(array $user): string
    {
        return "{$user['name']} <{$user['email']}>";
    }
}
```

## Guard methods — narrow types in callers

```php
/**
 * @phpstan-assert-if-true non-empty-string $value
 */
public function isNonEmptyString(mixed $value): bool
{
    return is_string($value) && $value !== '';
}
```

After `if ($validator->isNonEmptyString($x)) { ... }`, PHPStan treats `$x` as `non-empty-string` inside the block.
