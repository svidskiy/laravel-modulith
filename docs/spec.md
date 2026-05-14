# Modulith — VSA package design

## 1. Философия (4 правила, на них опирается всё остальное)

1. **Slice = папка-фича, всё для неё внутри.** Controller, model, migration, routes, views, тесты — рядом.
2. **Zero PHP-конфига для slice'а в 95% случаев.** Создал папку → работает.
3. **Single autoload, shared dependencies.** Один `composer.json` для всего app, slice'ы НЕ ship-able как отдельные packages.
4. **Это НЕ DDD.** Никаких Aggregate / Domain Event / Shared Kernel / Value Object. Slice — это просто folder with conventions.

---

## 2. Структура slice'а

**Минимум** (то, что генерит `modulith:make Billing`):

```
modules/Billing/
├── Http/Controllers/
├── Models/
├── routes/
│   └── web.php
└── database/migrations/
```

**Что может появиться по мере роста** (НЕ генерится автоматически, документируется как convention):

```
modules/Billing/
├── Actions/              ← single-action классы (CreateInvoice, RefundInvoice)
├── Console/Commands/     ← per-slice artisan commands
├── Http/
│   ├── Controllers/
│   ├── Requests/
│   └── Middleware/
├── Models/
├── Observers/            ← *Observer.php auto-bound to Models/*
├── Policies/             ← *Policy.php auto-bound to Models/*
├── Listeners/            ← регистрируются по атрибуту
├── Jobs/
├── View/Components/      ← Blade components, auto-prefixed
├── Contracts/            ← публичные интерфейсы для других slice'ов
├── config/
├── database/
│   ├── migrations/
│   └── factories/
├── lang/
├── resources/views/
├── routes/
│   ├── web.php
│   ├── api.php
│   └── console.php
├── Tests/                ← тесты slice'а живут со slice'ом
│   ├── Feature/
│   └── Unit/
└── middleware.php        ← опциональный файл с алиасами/группами
```

**Чего НЕ должно быть** (важнее, чем то, что должно):
- ServiceProvider в slice — если slice требует SP, slice уже разросся, разбей
- `Repositories/` / `Services/` как обязательные папки — провоцируют DDD-мышление
- `Domain/`, `ValueObjects/`, `Aggregates/` — DDD
- `Shared/`, `Common/` внутри slice'а — выноси в core `app/` или в отдельную slice

---

## 3. Auto-discovery (что подхватывается само)

| Артефакт | Путь | Механика |
|---|---|---|
| Web routes | `routes/web.php` | `loadRoutesFrom` + web middleware group |
| API routes | `routes/api.php` | `loadRoutesFrom` + api middleware group + `api/` prefix |
| Console routes | `routes/console.php` | closure-команды |
| Миграции | `database/migrations/*.php` | `loadMigrationsFrom` |
| Фабрики | `database/factories/*.php` | namespace-aware factory resolver |
| Config | `config/*.php` | merge с ключом `<slice>.<file>` |
| Views | `resources/views/` | namespace `<slice>::` |
| Translations | `lang/` | namespace `<slice>::` |
| Blade components | `View/Components/*.php` | prefix `<slice>:component-name` |
| Console commands | `Console/Commands/*.php` | auto-register если extends `Command` |
| Listeners | `Listeners/*.php` + `#[Listens(Event::class)]` | атрибут связывает event → listener |
| Observers | `Observers/*Observer.php` | `InvoiceObserver` → `Models\Invoice` |
| Policies | `Policies/*Policy.php` | `InvoicePolicy` → `Models\Invoice` |
| Middleware | `middleware.php` (return array) | aliases + groups |

**Чего осознанно НЕ авто-discover-ить:**
- Service container bindings — слишком implicit, теряешь следы
- Любые роуты кроме web/api/console — corner cases пусть user пишет руками
- Per-slice ServiceProvider — если нужен, пользователь регистрирует стандартно через `bootstrap/providers.php`

---

## 4. Naming conventions

| | Формат | Пример |
|---|---|---|
| Имя slice'а | StudlyCase | `Billing`, `OrderManagement` |
| Путь | `modules/StudlyCase/` | `modules/OrderManagement/` |
| Namespace | `Modules\StudlyCase\` | `Modules\OrderManagement\Models\Order` |
| Slice key (для view/lang/component prefix) | kebab-case | `Billing` → `billing`, `OrderManagement` → `order-management` |

Конкретно: `<x-billing:invoice-row />`, `view('billing::invoice.show')`, `__('billing::messages.paid')`.

---

## 5. Inter-slice communication

**Правила** (документируются, НЕ enforce-ятся пакетом в v1):

1. **Slice A не импортирует internals Slice B.** Допустимо: `Modules\B\Contracts\*`, `Modules\B\Models\*` (модели — публичны), `Modules\B\Events\*`.
2. **Cross-slice вызовы:**
   - **Async / fire-and-forget** → Laravel events: `event(new InvoicePaid($invoice))`
   - **Sync request/response** → интерфейс в `Modules\Billing\Contracts\BillingApi`, реализация рядом, биндинг в обычном `AppServiceProvider`
3. **Никакого "Shared" слоя.** Если два slice'а нуждаются в одном модуле — это либо новый slice (`Modules\Auth`), либо в core `app/`.

Опционально post-v1: shipped `deptrac.yaml` или `phparkitect` rules для проверки границ.

---

## 6. PSR-4 autoload

**Один маппинг в host-app `composer.json`:**

```json
{
    "autoload": {
        "psr-4": {
            "App\\": "app/",
            "Modules\\": "modules/"
        }
    }
}
```

`modulith:install` сам добавляет эту строку, идемпотентно. После — никакой runtime-магии:
- ✅ Optimized classmap (`composer dump-autoload -o`)
- ✅ Совместимо с `--classmap-authoritative` деплоем
- ✅ IDE/PHPStan/Psalm видят всё статически
- ✅ Refactor namespace через IDE работает

---

## 7. Artisan surface

**v1 (минимум):**
- `modulith:install` — добавляет PSR-4 маппинг, публикует config, создаёт `modules/`
- `modulith:make {Name}` — scaffold slice'а с минимумом папок
- `modulith:list` — таблица discovered slice'ов
- `modulith:cache` / `modulith:clear` — кэш manifest'а

**v0.x опционально:**
- `modulith:make:action {Slice} {Name}` — single-action класс в `Actions/`
- `modulith:make:controller {Slice} {Name}` — controller внутри slice'а
- `modulith:make:model`, `modulith:make:migration`, и т.д. — обёртки над Laravel `make:*`, которые подставляют правильный slice-namespace и путь

Ключевое: артизан-команды не дублируют Laravel, а делают то же самое, **зная контекст slice'а**.

---

## 8. Install / dev flow (как это выглядит для пользователя)

```bash
composer require svidskiy/laravel-modulith
php artisan modulith:install
# → adds "Modules\\": "modules/" to composer.json
# → publishes config/modulith.php
# → creates modules/ directory
# → prints: "Run 'composer dump-autoload' to finish setup"

composer dump-autoload

php artisan modulith:make Billing
# → modules/Billing/{Http/Controllers, Models, routes/web.php, database/migrations}

# Дальше: пишешь код, как в обычном Laravel.
# Любая Eloquent-модель в Models/, routes/ автоматически работают,
# миграции подхватываются. Никаких регистраций.
```

После initial setup — **нулевая ceremony**. Создал контроллер в `Modules\Billing\Http\Controllers\InvoiceController` → прописал в `modules/Billing/routes/web.php` → работает.

---

## 9. Что осознанно ВЫНЕСЕНО за рамки пакета

| Что | Почему не включаем |
|---|---|
| Per-module `composer.json` / merge-plugin | Это nWidart-territory, overkill для VSA — slice'ы не ship-able |
| Per-module ServiceProvider scaffold | Добавляет порог входа, 90% slice'ов в нём не нуждаются |
| CQRS / Mediator / Command bus | Это clean architecture, не VSA |
| Aggregate / Value Object / Domain Event | DDD, отдельная философия |
| Shared Kernel / Anti-Corruption Layer | DDD |
| Cross-slice access control enforcement | В v1 — только convention; post-v1 можно ship deptrac |
| Runtime PSR-4 (`addPsr4` в boot) | Ломает IDE/PHPStan/classmap-authoritative |
| GUI / dashboard для slice'ов | Из коробки не нужно, потом отдельный плагин |

---

## 10. Главное отличие от nWidart / Spatie modules

| | nWidart | Modulith (VSA) |
|---|---|---|
| Per-module composer.json | Да (v11+) | **Нет** |
| Свой ServiceProvider в модуле | Обязателен | **Не нужен** |
| Auto-discovery routes/views/migrations | Через SP в модуле | **Через convention scanning** |
| Изоляция dependencies | Да | **Нет (shared)** |
| Установка нового модуля | `module:make` + dump-autoload + manual SP edits | `modulith:make` + написал код, всё |
| Аналогия | Rails Engines, Symfony Bundles | **NestJS modules** |
| Целевая аудитория | Команды, идущие к microservices | Команды, которым нужна структура без распилки |
