# Laravel framework upgrade — current → 13.x (latest)

## Context

This project's `composer.json` declares `laravel/framework: ^12.0`, but `composer.lock` records the actually-installed version as **v11.23.5**. A constraint bump happened, but `composer update` was never run to resolve it. Meanwhile, **Laravel 13** has shipped (latest at time of writing: **v13.8.0**, released 2026-05-05) and the project is two majors behind in practice.

This document is the runbook for closing that gap and arriving at a clean Laravel 13 + PHP 8.4 codebase, with the modern slim skeleton (`bootstrap/app.php` fluent configuration, no `Http/Console/Exception` Kernel files).

It is intentionally a **runbook**, not an implementation plan: it is ordered, dependency-aware, and each phase has explicit exit criteria. Each phase should be implemented and verified in its own commit (or its own session) before the next is attempted.

---

## Decisions (already confirmed)

| Decision | Choice | Rationale |
|---|---|---|
| Target framework version | **Laravel 13** (latest stable) | Current upstream — unblocks future security/feature releases. |
| PHP minimum | **PHP 8.4** | Local dev already on 8.4.16. Avoids the L13.3+/Symfony-8/PHP-8.4 pin trap. Future-proofs the framework constraint. |
| Skeleton style | **Slim L11+ skeleton** (`bootstrap/app.php` fluent config) | Drops boilerplate (`Http/Console/Exception` kernels, four service providers). Aligns with current Laravel conventions. |

---

## Verified current state

| Aspect | Reality | Implication |
|---|---|---|
| Declared `laravel/framework` | `^12.0` | Composer says 12. |
| Locked `laravel/framework` | `v11.23.5` | Project is actually running 11. |
| Skeleton | Legacy (Laravel 10-style) — `bootstrap/app.php` binds Kernel singletons; `app/Http/Kernel.php`, `app/Console/Kernel.php`, `app/Exceptions/Handler.php`, `RouteServiceProvider`, `BroadcastServiceProvider`, `EventServiceProvider`, `AuthServiceProvider` all present. | Slim-skeleton migration is non-trivial. |
| `composer.json` `php` | `^8.2` | Needs bump to `^8.4`. |
| Dockerfile | `php:8.2-apache` | Needs bump to `php:8.4-apache`. |
| Local dev PHP | 8.4.16 | No local-machine prep work. |
| PHPUnit | `^11.0.1` | Needs bump to `^12.0`. |
| App-specific providers (must survive) | `AppServiceProvider`, `RepositoryServiceProvider`, `ServiceServiceProvider`, `HorizonServiceProvider` | Re-list in new `bootstrap/providers.php`. |
| Custom middleware aliases | `company` → `SetCompanyDatabaseConnection`, `developer` → `UserIsDeveloper`, `admin-or-developer` → `UserIsAdminOrDeveloper` (all in `app/Http/Kernel.php` lines 90–92) | Copy verbatim into `withMiddleware()->alias([...])`. |
| Rate limiter | `api` — 60/min by user→ip — defined in `RouteServiceProvider::configureRateLimiting()` lines 57–62 | Move to `AppServiceProvider::boot()`. |
| Routes | `routes/{api,web,console,channels}.php` | Loaded via `withRouting()` and `withBroadcasting()`. |
| `RouteServiceProvider::HOME` | `/home` | Unused (no auth scaffolding hits it); drop on delete. |
| `EventServiceProvider::$listen` | Only `Registered → SendEmailVerificationNotification` | Auto-registered by framework; can drop the provider. |
| `AuthServiceProvider::$policies` | Empty | Auto-discovery covers it; can drop. |
| `Exceptions/Handler.php` | Default `dontFlash` (`current_password`, `password`, `password_confirmation`); empty `dontReport`; empty `register()` body | Pass `dontFlash` to `$exceptions->dontFlash([...])`. |
| `config/filesystems.php` `local`/`public` disks | Explicit `root` paths | L12 default change to `storage/app/private` does not affect us. |
| `HasUuids` trait usage | None in `app/` | L12 UUIDv7 default switch does not affect us. |
| `image:` validation rule usage | None in `app/` | L12 SVG-exclusion change does not affect us. |
| Models with `boot()` override | `Notification`, `NotificationTopic`, `NotificationUser` — all only register event closures | Safe under L13's "no model instantiation in `boot()`" rule. |
| `Cache::remember` users | `CompanyService::setCompanyDatabaseConnection` (key `company_{id}`) — caches a derived array, not Eloquent models | L13 default `serializable_classes => false` is safe; no allow-list needed. |
| Cache/Redis prefix configs | Hardcoded with underscores | L13 default change to hyphens does not affect us — we keep the explicit configs. |

---

## Strategy: three sequential, independently-verifiable phases

Phases are committed separately so any breakage can be bisected.

```
Phase A → reconcile to L12 (just composer update)
Phase B → modernize skeleton (still on L12)
Phase C → bump to L13 + PHP 8.4
Phase D → verification (after each, especially C)
```

---

## Phase A — Reconcile to Laravel 12 (clean baseline)

**Goal:** put `composer.lock` and `composer.json` on the same major version before introducing further change. Keeps Phase B/C diffs interpretable.

### Steps

1. Audit dependencies that may need a bump for L12:
   - `php-open-source-saver/jwt-auth` — already on `^2.8.2`; verify it resolves.
   - `laravel/horizon`, `laravel/sanctum`, `laravel/tinker`, `laravel/ui` — verify L12 support.
   - `spatie/laravel-sluggable: ^3.7` — confirm L12 compat or bump to ^4.
   - `opcodesio/log-viewer: ^3.19` — supports `^12` already.
   - `doctrine/dbal: ^3.5` — should resolve under L12.
   - **Carbon 3.x** is mandatory under L12 — confirm transitive resolve.
2. Run `composer update` (no manual constraint changes yet — let composer resolve to L12 from the existing `^12.0` constraint).
3. Boot the app and exercise:
   - `php artisan about` → reports Laravel `12.x`.
   - `vendor/bin/phpunit` → green.
   - `npm run dev`; log in via SPA; switch company; load one CRUD page.

### Exit criteria

- `composer.lock` shows `laravel/framework: v12.x.y`.
- Test suite passes.
- SPA login + company switch + one tenant CRUD page works end-to-end.
- One commit: `chore(deps): reconcile Laravel framework to 12.x` (or similar).

---

## Phase B — Modernize the skeleton (L11+ slim layout)

**Goal:** drop Kernel/Provider boilerplate **while still on L12**. This lets the L13 bump in Phase C be a pure dependency change.

### File-by-file changes

**Rewrite `bootstrap/app.php`:**

```php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        apiPrefix: 'api',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'company'             => \App\Http\Middleware\SetCompanyDatabaseConnection::class,
            'developer'           => \App\Http\Middleware\UserIsDeveloper::class,
            'admin-or-developer'  => \App\Http\Middleware\UserIsAdminOrDeveloper::class,
        ]);
        // TrustProxies is now built-in; configure if needed via $middleware->trustProxies(...)
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->dontFlash([
            'current_password',
            'password',
            'password_confirmation',
        ]);
    })
    ->create();
```

**Create `bootstrap/providers.php`:**

```php
<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\RepositoryServiceProvider::class,
    App\Providers\ServiceServiceProvider::class,
    App\Providers\HorizonServiceProvider::class,
];
```

**Modify `app/Providers/AppServiceProvider.php`** — host the rate limiter relocated from `RouteServiceProvider`:

```php
public function boot(): void
{
    if ($this->app->environment('production') || $this->app->environment('development')) {
        URL::forceScheme('https');
    }

    RateLimiter::for('api', function (Request $request) {
        return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
    });
}
```

**Modify `config/app.php`** — remove the four legacy providers from `'providers'`. The new `bootstrap/providers.php` is the source of truth.

**Delete:**

- `app/Http/Kernel.php`
- `app/Console/Kernel.php`
- `app/Exceptions/Handler.php`
- `app/Providers/RouteServiceProvider.php`
- `app/Providers/BroadcastServiceProvider.php`
- `app/Providers/EventServiceProvider.php`
- `app/Providers/AuthServiceProvider.php`

**Untouched on purpose:** `app/Http/Middleware/*` (the alias targets stay), `app/Providers/{App,Repository,Service,Horizon}ServiceProvider.php`, `routes/*.php`, `database/migrations/*`.

### Exit criteria

- `php artisan about` boots without errors and lists the four surviving app providers.
- `php artisan route:list` enumerates all ~230 routes.
- `company`, `developer`, `admin-or-developer` middleware aliases resolve (request a route guarded by each; check 422/403 behavior matches pre-upgrade).
- `vendor/bin/phpunit` green.
- One commit: `refactor: adopt Laravel 11+ slim application skeleton`.

---

## Phase C — Bump to Laravel 13 + PHP 8.4

### `composer.json` changes

```json
{
  "require": {
    "php": "^8.4",
    "laravel/framework": "^13.0",
    "laravel/horizon": "^5.x",
    "laravel/sanctum": "^4.x",
    "laravel/tinker": "^3.0",
    "laravel/ui": "^4.x",
    "spatie/laravel-sluggable": "^4.0",
    "doctrine/dbal": "^4.0",
    "php-open-source-saver/jwt-auth": "^2.9",
    "opcodesio/log-viewer": "^3.x"
  },
  "require-dev": {
    "phpunit/phpunit": "^12.0",
    "laravel/sail": "^1.x"
  }
}
```

**Verify at upgrade time** (versions move):

- `laravel/horizon`, `laravel/sanctum`, `laravel/ui` — open Packagist and confirm the latest tag has L13 in its `illuminate/*` constraints.
- `akaunting/laravel-money: ^6.0`, `wildbit/postmark-php: ^6.0`, `awobaz/compoships: ^2.2`, `google/cloud-translate: ^1.19`, `predis/predis: ^2.2`, `league/flysystem: ^3.18`, `league/flysystem-ftp: ^3.16`, `guzzlehttp/guzzle: ^7.2` — check each on Packagist for L13 compat; bump majors as needed.
- `fakerphp/faker: ^1.9.1`, `mockery/mockery: ^1.4.4` — verify against PHPUnit 12.

Run `composer update` and resolve any platform errors.

### Code/config changes triggered by L13

| Change | Required action | Where |
|---|---|---|
| CSRF middleware rename: `VerifyCsrfToken` → `PreventRequestForgery` | The legacy alias still works. Optional rename later for cleanliness. | n/a (no project override) |
| `Cache` `serializable_classes` defaults to `false` | Audit `Cache::remember`/`Cache::put`. Only `CompanyService` uses cache and stores derived arrays. Add `'serializable_classes' => false` to `config/cache.php` to make the default explicit. | `config/cache.php`, `app/Services/Company/CompanyService.php` (re-verify the cached payload is plain arrays/scalars) |
| Polymorphic pivot pluralization | grep for `morphTo`, `morphedByMany`, `morphToMany` and verify table names match expectations. Project has none currently — confirm during upgrade. | `app/Models/**` |
| MySQL `DELETE` with JOIN/ORDER/LIMIT now compiles full query | grep for `->join(...)->delete()` and `->orderBy(...)->limit(...)->delete()` chains; rewrite if any rely on the old ignored-clauses behavior. | `app/Repositories/**`, `app/Services/**` |
| `upsert()` requires non-empty `uniqueBy` | grep for `->upsert(`; confirm every call passes a non-empty array. | `app/**` |
| Carbon-only date manipulation | already covered by Phase A (Carbon 3.x is required from L12 onward). | n/a |
| `Js::from` switches to `JSON_UNESCAPED_UNICODE` | Project is SPA-only with no Blade output; negligible. | n/a |
| Pagination Bootstrap-3 view rename | Project doesn't override pagination views. | n/a |

### Infrastructure changes

| File | Change |
|---|---|
| `Dockerfile` | `FROM php:8.2-apache` → `FROM php:8.4-apache`. Re-verify `docker-php-ext-install pdo_mysql zip sodium ftp pcntl` and `pecl install redis` against the 8.4 base. |
| `.devcontainer/docker-compose.yml` | Update PHP image / service definition if it pins 8.2. |
| `README.md` | "Using PHP 8 and VUE 3" → "Using PHP 8.4 and Vue 3". `valet isolate php@8.2` → `valet isolate php@8.4`. |
| `CLAUDE.md` | Bump the "Laravel 12 (PHP 8.2)" line in the "Tech stack" section to "Laravel 13 (PHP 8.4)". Update the skeleton note (no longer legacy). |

### Exit criteria

- `php artisan about` shows Laravel `13.x` and PHP `8.4.x`.
- All Phase D verification steps pass.
- One commit: `chore(deps): upgrade to Laravel 13 + PHP 8.4`.

---

## Phase D — Verification

Run after **every** phase, but treat the post-Phase-C run as the gating one for the upgrade.

### Backend

1. `composer install` clean — no platform errors.
2. `composer outdated --direct` — no remaining majors blocking.
3. `php artisan about` — Laravel 13.x, PHP 8.4.x, providers list looks right.
4. `vendor/bin/phpunit` — all suites green.
5. `php artisan route:list` — enumerates all ~230 API routes.
6. `php artisan horizon:status` — boots without exceptions.

### Frontend

7. `npm install && npm run build` — succeeds.
8. `npm run dev` — Vite dev server boots, HMR working.

### End-to-end smoke

9. **Login (JWT)** — `POST /api/auth/login` returns a token; `POST /api/auth/user` returns the user.
10. **Company switch (multi-tenant)** — load a tenant CRUD page (Customer or Item list). This exercises `SetCompanyDatabaseConnection` middleware via the `CompanyId` request parameter.
11. **Office-data CRUD** — load a non-tenant page (Roles or Module Settings).
12. **Cache integrity** — `php artisan cache:clear`; hit a tenant endpoint twice; verify the second request stays under the SQL load of the first (proves `Cache::remember('company_…')` round-trips).
13. **Docker build** — `docker build -t nvisionoffice:l13 .` succeeds; container boots; `/api/auth/login` returns 200 against a real DB.

### Exit criteria

All 13 checks above pass on a clean checkout.

---

## Critical files to modify or delete

| Action | Path | Phase |
|---|---|---|
| Modify | `composer.json` | A, C |
| Re-resolve | `composer.lock` | A, C |
| Rewrite | `bootstrap/app.php` | B |
| Create | `bootstrap/providers.php` | B |
| Modify | `app/Providers/AppServiceProvider.php` (host the `api` rate limiter) | B |
| Modify | `config/app.php` (remove provider entries now in `bootstrap/providers.php`) | B |
| Modify | `config/cache.php` (add `'serializable_classes' => false`) | C |
| Delete | `app/Http/Kernel.php` | B |
| Delete | `app/Console/Kernel.php` | B |
| Delete | `app/Exceptions/Handler.php` | B |
| Delete | `app/Providers/RouteServiceProvider.php` | B |
| Delete | `app/Providers/BroadcastServiceProvider.php` | B |
| Delete | `app/Providers/EventServiceProvider.php` | B |
| Delete | `app/Providers/AuthServiceProvider.php` | B |
| Modify | `Dockerfile` (PHP 8.4) | C |
| Modify | `.devcontainer/docker-compose.yml` (PHP 8.4) | C |
| Modify | `README.md` (PHP version + Valet command) | C |
| Update | `CLAUDE.md` (Laravel/PHP versions, skeleton note) | C |

---

## Reused existing utilities and patterns

- **Slim-skeleton boilerplate template:** the Laravel 13 skeleton on GitHub — `https://github.com/laravel/laravel/blob/13.x/bootstrap/app.php` — is the starting reference for the rewrite.
- **App provider list** to register in `bootstrap/providers.php` is enumerated in the table above.
- **Middleware aliases** pre-exist in `app/Http/Kernel.php` lines 90–92 — copy verbatim into `withMiddleware()->alias([...])`.
- **Rate limiter logic** pre-exists in `app/Providers/RouteServiceProvider.php` lines 57–62 — copy verbatim into `AppServiceProvider::boot()`.
- **`dontFlash` array** pre-exists in `app/Exceptions/Handler.php` lines 24–28 — pass to `$exceptions->dontFlash([...])`.
- The four custom middleware classes (`SetCompanyDatabaseConnection`, `UserIsDeveloper`, `UserIsAdminOrDeveloper`, `Authenticate`) are already PSR-4 classes — no rewriting needed; they just stop being registered through `app/Http/Kernel.php` and start being registered through `bootstrap/app.php`.

---

## Sources

- Laravel 12→13 upgrade guide — https://laravel.com/docs/13.x/upgrade
- Laravel 11→12 upgrade guide — https://laravel.com/docs/12.x/upgrade
- Laravel 13 release notes — https://laravel.com/docs/13.x/releases
- Laravel versions / PHP requirements — https://laravelversions.com/en, https://laravel-news.com/laravel-13-released
- jwt-auth L13 compat — https://packagist.org/packages/php-open-source-saver/jwt-auth (v2.9.0 declares `illuminate/auth: ^12|^13`)
- spatie/laravel-sluggable L13 compat — https://github.com/spatie/laravel-sluggable (v4.0.2 supports `^12|^13`)
- opcodesio/log-viewer L13 compat — https://github.com/opcodesio/log-viewer (constraints include `^13.0`)
