# Laravel 13 and PHP 8.4 Upgrade Plan

## Summary

Upgrade the project from PHP `^8.2` / Laravel `^12.0` to PHP `^8.4` / Laravel `^13.0`, while preserving the current
classic Laravel skeleton and JWT `auth:api` contract.

## Key Changes

- Update Composer constraints for Laravel 13, PHP 8.4, Tinker 3, JWT auth 2.9, and PHPUnit 12.
- Keep `auth:api`, `config/auth.php`, `config/jwt.php`, and `App\Models\Office\User implements JWTSubject` functionally
  unchanged.
- Replace direct CSRF middleware references with Laravel 13 `PreventRequestForgery`.
- Add explicit `cache.serializable_classes` config for intentionally cached model objects.
- Future-proof MySQL SSL CA PDO constants for runtimes allowed by `^8.4`.
- Keep Docker out of scope.

## Test Plan

- `composer validate`
- `php artisan package:discover`
- `php artisan config:clear`
- `php artisan route:list`
- `vendor/bin/phpunit`
- Manual JWT checks for login, authenticated API access, and logout.

## Assumptions

- API token response shape and Bearer JWT behavior must not change.
- The classic `bootstrap/app.php`, `Http\Kernel`, and `RouteServiceProvider` structure stays in place.
- Cached Eloquent objects are kept for this upgrade; a later hardening pass can migrate them to arrays or DTOs.
