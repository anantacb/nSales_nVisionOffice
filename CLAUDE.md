# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Tech stack

Laravel 13 (PHP 8.4) backend serving an API, with a Vue 3 + Vite SPA frontend. JWT auth (`php-open-source-saver/jwt-auth`), Pinia, Vue Router, Bootstrap 5.2. Default cache/queue drivers are `file`/`sync`; Redis + Horizon are configured for production. Local Valet host: `nvisionoffice.test`.

## Common commands

```bash
# First-time setup
composer install
php artisan key:generate
php artisan jwt:secret
php artisan log-viewer:publish
npm install

# Dev
npm run dev                      # Vite dev server (HMR via laravel-vite-plugin, Valet TLS)
npm run build                    # Production bundle into public/build/

# Tests (PHPUnit 12)
vendor/bin/phpunit               # All suites
vendor/bin/phpunit --testsuite=Unit
vendor/bin/phpunit --testsuite=Feature
vendor/bin/phpunit --filter SomeTest::method_name

# Queues / scheduled work
php artisan horizon              # Redis-backed queue worker dashboard

# Custom artisan commands live in app/Console/Commands/
# (Email/, Make/, Postmark/, Translation/, plus copy-DBs-prod-to-dev, etc.)
```

If PHP isn't 8.4 by default: `valet isolate php@8.4`.

## Multi-tenant database architecture (the most important thing to understand)

This app talks to **multiple MySQL databases** at once. The split is enforced via separate connections in `config/database.php`:

| Connection | Purpose | Models live in |
|---|---|---|
| `mysql` (default, `NVISION_OFFICE`) | Central tenant registry: companies, users, modules, applications, roles, settings, languages | `app/Models/Office/` |
| `mysql_company` | The currently-active tenant's database (swapped per request) | `app/Models/Company/` |
| `mysql_admin` (`NSALES_ADMIN`) | Cross-tenant admin (e.g. FTP users) | `app/Models/Admin/` |
| `mysql_dev` | Dev mirror used when copying prod → dev | n/a |

`mysql_company` is **rebound at runtime** by `App\Http\Middleware\SetCompanyDatabaseConnection` (route alias `company`). Every API request that touches tenant data must include a `CompanyId` parameter and run through that middleware. The actual swap is `CompanyService::setCompanyDatabaseConnection($id)` which:

1. Resolves the `Company` row (cached 24h under `company_{id}`),
2. Calls `DbHelpers::connectDB()` for local databases or `DbHelpers::connectCloudSqlDB()` for Google Cloud SQL tenants,
3. Decrypts the per-tenant DB password using `DB_ENCRYPTION_KEY` for cloud tenants.

`NVISION_TEMPLATE` (env: `DB_TEMPLATE_DATABASE`) is the **schema reference** new tenant DBs are cloned from. Tenant schemas are managed dynamically through `TableController` / `TableFieldController` / `TableIndexController` using `App\Helpers\Sql\MysqlQueryGenerator` — that's why `database/migrations/` only contains the four baseline Laravel files. **Do not add app schema by writing Laravel migrations.**

When writing code that reads/writes tenant data, ensure your model extends `BaseModel` and lives under `app/Models/Company/` (its `$connection` resolves through `mysql_company`). Office-side code uses the default connection — don't cross the streams.

## Layered code structure

Controllers → Services (interface-bound) → Repositories (interface-bound) → Models. Bindings are explicit:

- `App\Providers\ServiceServiceProvider` — binds every `*ServiceInterface` to its concrete `*Service`.
- `App\Providers\RepositoryServiceProvider` — binds every `*RepositoryInterface` to its concrete `*Repository`.
- When you add a new service or repository, register the binding in the matching provider or DI will fail.

Services return `App\Contracts\ServiceDto { string $message; int $statusCode; mixed $data }`. Controllers unwrap this and shape the JSON response — keep that contract when adding service methods.

Repository tree mirrors the DB split: `app/Repositories/Eloquent/{Office,Company,Admin}/` plus `app/Repositories/Plugin/` for external HTTP integrations (Postmark, BunnyCdn, B2bGqlApi, NsalesOfficeRestApi, NvmGqlApi, NsalesAdminDjangoApi).

`BaseRepository::paginatedData()` is the standard query interface — it accepts a structured request (selected_columns, relations, filters, filter_by_relation, search, order, pagination). Reuse it instead of hand-rolling list endpoints.

## Model conventions

`App\Models\BaseModel` (extended by all app models) sets:
- Primary key `Id` (capitalized).
- Timestamps `InsertTime` / `UpdateTime`, soft-delete column `DeleteTime`.
- `$guarded = []` (mass-assignment is open by design — validation happens in `App\Http\Requests\*`).
- Date serialization to `Y-m-d H:i:s`.

Stick to PascalCase column names (`CompanyId`, `Name`, etc.) — the rest of the codebase assumes it.

## Routes & middleware

All real endpoints are POST under `routes/api.php` (~230 routes). `routes/web.php` is just a `{any}` catch-all serving the SPA shell view. Key route middleware aliases (see `app/Http/Kernel.php`):

- `auth:api` — JWT-protected.
- `company` → `SetCompanyDatabaseConnection` (requires `CompanyId` in payload).
- `developer` → `UserIsDeveloper`.
- `admin-or-developer` → `UserIsAdminOrDeveloper`.

## Frontend (resources/js)

- Single SPA: `app.js` mounts on `#app`, all routing via `vue-router` with lazy-imported views.
- Path alias `@/` → `resources/js/*` (configured in `jsconfig.json` and Vite). Use it.
- State stores under `resources/js/stores/`: `authStore`, `companyStore`, `notificationStore`, `templateStore`. Tenant switching on the frontend goes through `companyStore` and is reflected in API calls via the `CompanyId` parameter.
- `useCheckAccess` composable enforces module/permission checks in route guards.
- Vite config (`vite.config.js`) uses `valetTls: 'nvisionoffice.test'` and aliases `vue` to the ESM bundler build (so `<template>` strings work at runtime if anything compiles them).

## Globally autoloaded helpers

`composer.json` autoloads these files on every request — their functions are global:

- `app/Helpers/Functions/dataChecker.php`
- `app/Helpers/Functions/randomGenerator.php`
- `app/Helpers/Functions/postmarkTokenEncryptionDecryption.php`
- `app/Helpers/Functions/translator.php`

`app/Override/Postmark/` contains a local override of the `wildbit/postmark-php` package — be aware before changing Postmark behavior.

## Setup gotchas

- `JWT_SECRET` and `APP_KEY` must be generated before the app boots; otherwise auth and encryption fail silently in places.
- `DB_ENCRYPTION_KEY` must be a valid Laravel encryption key (matches `config('app.cipher')`) for cloud-tenant DB password decryption to work.
- For tenant-data work locally, ensure both `NVISION_OFFICE` and the target tenant DB exist on your MySQL instance; the middleware will throw "DB not found" otherwise (logged, not raised) and you'll see empty results.
