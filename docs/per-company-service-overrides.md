# Per-Company Service Override Resolution

## Context

The app is multi-tenant. The `SetCompanyDatabaseConnection` middleware swaps the `mysql_company` connection to the active tenant's database based on the `CompanyId` payload that accompanies every API call. Today, every service interface in `ServiceServiceProvider` is bound 1:1 to a single concrete class via `$this->app->bind(...)`, so every tenant runs identical business logic.

We want tenant-specific behaviour without forking entire service files: keep one base service per domain, and let a company-specific subclass override only the methods that need to differ. When `CompanyA` is the active tenant, resolving `WebShopUserServiceInterface` must yield `App\Services\WebShopUser\CompanyA\WebShopUserService` (which extends the base). If no override class exists for the active tenant, the base service resolves as it does today.

Scope:

- **Services only** — repositories keep their current 1:1 binding.
- **Only tenant-data services** — i.e. the 16 services that actually read or write through `mysql_company`. The 23 office services and 3 mixed services remain on the existing binding pattern.

## Approach

Convention-driven override discovered at container-resolution time, keyed by `Company.DomainName`.

- **Base service** stays at `app/Services/{Domain}/{Domain}Service.php` (e.g. `app/Services/WebShopUser/WebShopUserService.php`).
- **Override** lives at `app/Services/{Domain}/{Company.DomainName}/{Domain}Service.php` with namespace `App\Services\{Domain}\{Company.DomainName}` and `extends` the base. Each override only re-declares the methods it changes; everything else is inherited.
- **Resolver helper** computes the candidate override class name at runtime and falls back to the base when the class doesn't exist.
- **`ServiceServiceProvider`** gains a `bindOverridable($interface, $baseConcrete)` helper that registers a `bind()` closure delegating to the resolver. Each of the 16 tenant-data services switches from `bind(...)` to `bindOverridable(...)`.

Because `SetCompanyDatabaseConnection` middleware runs **before** the controller's constructor injection, the active `CompanyId` is already on the request when the binding closure fires. The resolver reads it from `request()->input('CompanyId')` and uses the existing 24-hour `company_{id}` cache populated by `CompanyService::setCompanyDatabaseConnection()` to find `DomainName` — no extra database queries.

## Files

### New: `app/Services/Resolvers/CompanyServiceResolver.php`

```php
namespace App\Services\Resolvers;

use Illuminate\Support\Facades\Cache;

class CompanyServiceResolver
{
    public static function resolveConcrete(string $baseConcrete): string
    {
        $domain = self::activeDomainName();
        if ($domain === null) {
            return $baseConcrete;
        }

        $pos = strrpos($baseConcrete, '\\');
        $namespace = substr($baseConcrete, 0, $pos);
        $shortName = substr($baseConcrete, $pos + 1);
        $candidate = "{$namespace}\\{$domain}\\{$shortName}";

        return class_exists($candidate) ? $candidate : $baseConcrete;
    }

    private static function activeDomainName(): ?string
    {
        $companyId = request()?->input('CompanyId');
        if (!$companyId) {
            return null;
        }

        $company = Cache::get('company_' . $companyId);
        return $company?->DomainName ?: null;
    }
}
```

The cache key (`company_{id}`) is the same one `CompanyService::setCompanyDatabaseConnection()` populates, so there are no extra round trips. If `CompanyId` is absent (login, public routes) or the cache lookup returns null, the base concrete is returned — preserving current behaviour for non-tenant routes.

### Modified: `app/Providers/ServiceServiceProvider.php`

Add a private helper:

```php
private function bindOverridable(string $interface, string $baseConcrete): void
{
    $this->app->bind($interface, function () use ($baseConcrete) {
        $concrete = \App\Services\Resolvers\CompanyServiceResolver::resolveConcrete($baseConcrete);
        return $this->app->make($concrete);
    });
}
```

In `boot()`, replace `$this->app->bind(...)` with `$this->bindOverridable(...)` for these 16 interfaces:

| Interface | Base concrete |
|---|---|
| `WebShopUserServiceInterface` | `WebShopUserService` |
| `CustomerServiceInterface` | `CustomerService` |
| `CustomerVisitServiceInterface` | `CustomerVisitService` |
| `OrderServiceInterface` | `OrderService` |
| `OrderByCustomerServiceInterface` | `OrderByCustomerService` |
| `OrderByItemServiceInterface` | `OrderByItemService` |
| `ItemServiceInterface` | `ItemService` |
| `ItemAttributeServiceInterface` | `ItemAttributeService` |
| `WebShopPageServiceInterface` | `WebShopPageService` |
| `WebShopTextServiceInterface` | `WebShopTextService` |
| `WebShopLanguageServiceInterface` | `WebShopLanguageService` |
| `CompanyLanguageServiceInterface` | `CompanyLanguageService` |
| `CompanyTranslationServiceInterface` | `CompanyTranslationService` |
| `CompanyEmailLayoutServiceInterface` | `CompanyEmailLayoutService` |
| `CompanyEmailTemplateServiceInterface` | `CompanyEmailTemplateService` |
| `DocumentApiServiceInterface` | `DocumentApiService` |

The remaining 26 bindings (23 office services plus 3 mixed: `CompanyService`, `EmailLayoutService`, `EmailTemplateService`) stay on plain `$this->app->bind(...)`. Mixed services orchestrate cross-tenant work; converting them would surprise consumers. Convert them later by changing a single line in this provider if a real need emerges.

## Controllers — no changes

The override lives entirely in the IoC container's `bind()` closure, so controllers stay exactly as they are. Every tenant-data controller already injects the *interface* (verified in `WebShopUserController`, `CustomerController`, `OrderController`, `OrderByCustomerController`):

```php
public function __construct(WebShopUserServiceInterface $service)
{
    $this->service = $service;
}
```

Request flow:

```
SetCompanyDatabaseConnection middleware
  ↓ (CompanyId on request; company_{id} cache populated)
Laravel resolves controller dependencies
  ↓
Container hits bind() closure for WebShopUserServiceInterface
  ↓
Closure calls CompanyServiceResolver::resolveConcrete(WebShopUserService::class)
  ↓
Resolver checks whether App\Services\WebShopUser\{DomainName}\WebShopUserService exists
  ↓  yes → returns override class
  ↓  no  → returns base class
Container instantiates that class with its dependencies and injects it
  ↓
Controller receives the correct implementation; knows nothing about the swap
```

Route definitions, middleware, form requests, and the API response shape are likewise unaffected. The two scenarios that *would* require a controller change do not apply here:

1. Controllers that resolve the concrete class directly (`app(WebShopUserService::class)`) instead of the interface would bypass the binding. None of the existing tenant-data controllers do this.
2. Controllers that act on multiple tenants in one request would need to re-resolve per tenant. Out of scope — `SetCompanyDatabaseConnection` enforces a single `CompanyId` per request.

## Authoring an override

Drop a file at `app/Services/{Domain}/{Company.DomainName}/{Domain}Service.php`, extend the base, redefine only the methods that differ:

```php
namespace App\Services\WebShopUser\Acme;

use App\Contracts\ServiceDto;
use Illuminate\Http\Request;

class WebShopUserService extends \App\Services\WebShopUser\WebShopUserService
{
    public function details(Request $request): ServiceDto
    {
        // Acme-only pre-processing here
        return parent::details($request);
    }
}
```

No provider edits needed. Hit the endpoint with `CompanyId` belonging to the tenant whose `DomainName === 'Acme'` and the override resolves automatically.

## Constraints

- **`DomainName` must be a valid PHP namespace segment** — pattern `[a-zA-Z_][a-zA-Z0-9_]*`. Tenants whose `DomainName` contains hyphens or punctuation cannot host an override file because the namespace would be invalid PHP. The resolver silently falls back to the base in that case (no runtime crash). Existing usage of `DomainName` (FTP paths, storage paths, FTP usernames) already implies slug-style values, so this should be a non-issue in practice.
- **Override class short name must match the base exactly** — e.g. `WebShopUserService`, not `AcmeWebShopUserService`. The resolver builds the candidate by inserting the `DomainName` namespace segment, not by rewriting the class name.
- **Overrides must extend the base** so unredefined methods inherit. A side-by-side implementation that re-implements the interface from scratch would technically satisfy the type, but lose the inheritance shortcut this design exists for.

## Verification

1. **No regressions on existing routes** — `vendor/bin/phpunit`. With no override files present, every binding still resolves to its base class.
2. **Override resolution end-to-end**:
   - Pick a real company; note its `DomainName` (e.g. `Acme`).
   - Create `app/Services/WebShopUser/Acme/WebShopUserService.php` extending the base. Override `details()` to return a `ServiceDto` with a sentinel message (`'OVERRIDE_HIT'`).
   - POST to the WebShopUser details route with that company's `CompanyId` — response message must be `OVERRIDE_HIT`.
   - POST with a different `CompanyId` (no override file for its `DomainName`) — response uses base behaviour.
   - Delete the override file; smoke-test repeats with base behaviour for both companies.
3. **Cache fallback safety** — `Cache::forget('company_' . $id)` for the test tenant, then reissue the request. `CompanyService::setCompanyDatabaseConnection()` repopulates the cache before the binding closure runs (middleware order), so the override should still resolve. If it falls back to base, the cache-population sequence has changed and the resolver needs to call `CompanyService::setCompanyDatabaseConnection()` itself (currently unnecessary).

## Out of scope (defer)

- Repository-level overrides (`RepositoryServiceProvider`).
- Conversion of the 3 mixed services.
- Frontend changes — none required.
- A registry table or admin UI listing which tenants have overrides; rely on `find app/Services -type d -mindepth 2` for now.