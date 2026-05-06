---
name: phpdoc
description: Add or refine PHPDoc docblocks. Use when the user asks to add docblocks, write generics, document throws, or sharpen types.
---

Write a PHPDoc only when it expresses MORE than the native signature.

## Goal — show with one example

```php
/**
 * Load every record of a given model class.
 *
 * @template T of Model
 *
 * @param  class-string<T>  $class
 * @return Collection<int, T>
 *
 * @throws ModelNotFoundException
 */
public function loadAll(string $class): Collection
```

The native signature `(string $class): Collection` doesn't say *which* class-string, what fills the collection, or that it throws. The PHPDoc carries that — and only that.

## When a docblock earns its place

- Generic types: `array<K, V>`, `Collection<int, User>`, `iterable<string>`, `class-string<T>`
- Array shapes: `array{id: int, name: string}`
- Callable signatures: `callable(int, string): bool`
- More specific scalars: `non-empty-string`, `positive-int`, `int<0, 100>`
- `@throws` for exceptions callers should handle (not every possible `RuntimeException`)
- `@template` / `@implements` / `@extends` for generic classes
- One-line summary above tags — only when behavior is non-obvious from the name

## Tag order (Pint laravel preset enforces)

`@param` → `@return` → `@throws`, with **2 spaces** between the tag and the type.

## Nullable

- Single nullable: `?Type` (matches PHP signature syntax — dominant in laravel/framework source)
- Inside a union: `Foo|Bar|null`

## Auto-generate, never hand-write

- Eloquent `@property` / `@property-read` blocks: `vendor/bin/testbench ide-helper:models -RW`
- Facade `@method static`: `vendor/bin/testbench ide-helper:generate`

For Laravel apps, replace `vendor/bin/testbench` with `php artisan`.

## Drop these

- `@param string $name` on a `string $name` parameter — Pint deletes it
- `@return mixed` with no narrowing
- `@var Foo $x` on typed properties — fix the source type
- `@author`, `@since`, `@version` — dead tags
- Docblock that paraphrases the method name (`/** Get the user. */ public function getUser()`)

## Workflow

1. Read the target file.
2. For each method/property, ask: *"does the signature already say everything?"* If yes, skip.
3. Write the minimum PHPDoc that adds the missing info.
4. `composer format:test` — if Pint flags `no_superfluous_phpdoc_tags`, **delete** the tag, don't tweak it.
5. `composer analyse` — verify generics resolve.

## Reference

Concrete patterns (generic repository, Eloquent model, facade `@method`, type-alias sharing, guard methods): [reference.md](reference.md).
