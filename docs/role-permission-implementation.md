# Role-permission access control — implementation runbook

## Context

The current authorization model is **role-type-based**: capabilities are implied by the literal value of `Role.Type` (`"Developer"`, `"Administrator"`), and checks are scattered across two near-identical middleware classes (`UserIsDeveloper`, `UserIsAdminOrDeveloper`), one Horizon gate, and a handful of `in_array('Developer', …)` guards inside services. Custom role Types carry zero implicit capabilities, so business-specific roles like `"Supervisor"` or `"Salesperson"` are unable to gate any backend action without code changes.

A `Permission` table now exists in the central `NVISION_OFFICE` database. The goal of this runbook is to wire that table into a working **permission system** with one clear rule:

> **Developer role-Type skips every permission check.** Administrator role-Type skips every check **except** permissions flagged `IsDeveloperOnly = 1` (schema/platform/ops operations stay developer-exclusive). Every other role must hold the matching permission to access a feature. Backend, frontend, menus, and route guards all follow this rule.

For background on how the existing role system works today (data model, services, middleware, frontend stores), see `docs/role-system.md`.

---

## Schema (already in place)

The `NVISION_OFFICE` schema described below is the **current** shape of the role/permission tables — no DDL is pending. The `Role.Type` ENUM, the `RolePermission` PK/timestamps/unique index, and the `Permission.Description` + `Permission.IsDeveloperOnly` columns have all been applied. The legacy `CompanyUserRolePermission` table was dropped (per-user permission overrides are out of scope — see the Decisions table).

| Table | As-built columns |
|---|---|
| `Role` | `Id, CompanyId, Name, Type, Description, InsertTime/UpdateTime/DeleteTime`. `Type` is `ENUM('Developer','Administrator','Manager','Employee','Client','Retailer','WebShopViewer','Insights','Marketing') NOT NULL`. |
| `CompanyUserRole` | `Id, CompanyUserId, RoleId, InsertTime/UpdateTime/DeleteTime`. |
| `RolePermission` | `Id PK, RoleId, PermissionId, InsertTime/UpdateTime/DeleteTime`, with `UNIQUE(RoleId, PermissionId)` and indexes on `RoleId` / `PermissionId`. |
| `RoleModule` | `Id, RoleId, ModuleId, Enabled, InsertTime/UpdateTime/DeleteTime`. Module-level access toggle, sibling concept to permissions; outside the scope of this runbook. |
| `Permission` | `Id, Name, Aliases, ApplicationId, ModuleId, Description, IsDeveloperOnly, InsertTime/UpdateTime/DeleteTime`. `Aliases` is the slug the middleware compares against (e.g. `"Order.Create"`). `IsDeveloperOnly = 1` marks platform/schema/ops permissions where Administrator bypass does **not** apply. |

The `Permission` table is empty today — populating its catalog (Phase A's `PermissionSeeder`) is the first executable step.

---

## Data model & cardinality

The permission system must respect the existing relations:

```
User  (1) ──< CompanyUser  (N per User, one per tagged Company)
                 │
                 └──< Role  (N per CompanyUser, via CompanyUserRole pivot)
                           │
                           └──< Permission  (N per Role, via RolePermission pivot — new)
```

**What this means operationally:**

- A User is tagged with **one or more Companies** through `CompanyUser`. Each tagging is independent — a user can be a Developer in Company A and an Employee in Company B.
- Within a single Company tagging, a User can hold **one or more Roles** simultaneously. The effective capability is the **union** of all those roles' permissions and role-Types.
- **Bypass triggers per-company**: if *any* of the user's roles in the currently selected company has `Type='Developer'`, every permission check is skipped for that company. If the highest tier is `Type='Administrator'`, every check is skipped *except* those whose permission is `IsDeveloperOnly=1`. The bypass does *not* carry across companies.
- The active-company permission/role set in the SPA always reflects the **currently selected company**, not the union across all of the user's companies. Switching companies swaps both arrays.

Reference: `app/Models/Office/{User,CompanyUser,CompanyUserRole,Role}.php`.

---

## How a role grant reaches the user

This is the chain a grant traverses from `RolePermission` to the SPA gate:

```
User → companyUsers (has-many) → roles (belongs-to-many via CompanyUserRole)
                                  → permissions (belongs-to-many via RolePermission)
```

Model wiring required for the chain to resolve: `User::companyUsers()` and `CompanyUser::roles()` already exist; **`Role::permissions()` is the one new relation** (Phase A). With it in place, both the permission middleware and `CompanyService::getAuthUserCompanies` perform the same single eager-load:

```php
User::with('companyUsers.roles.permissions:Id,Aliases')->find($userId);
```

**Per-company union.** A CompanyUser can hold multiple Roles. The effective permission set for that company is the union of all those roles' `Permission.Aliases`. If Role X grants `Order.Read` and Role Y grants `Order.Create`, the user's set in that company is `{Order.Read, Order.Create}`. The set is scoped to the company — switching company swaps it, and bypass (Developer / Administrator) triggers per company, not globally.

**Frontend handoff.** `CompanyService::getAuthUserCompanies` ships each company tagging with `permissions: [slug, …]` (Phase D). `companyStore.setSelectedCompanyById` forwards the active company's array to `authStore.setPermissions`. `useCheckAccess.hasPermission(slug)` then reads from `authStore.getPermissions` — that is the only path by which a UI element, a route guard, or a menu node sees a permission. There is no per-user override layer and no separate auth endpoint: permissions ride on the same payload as roles.

---

## Decisions

| Decision | Choice | Rationale |
|---|---|---|
| Authorization unit | **Permission**, a `(Resource, Action)` pair (e.g. `Order.Create`) | Maps to existing Module/Table taxonomy; uniform for both UI and backend gating. |
| Bypass roles | `Type='Developer'` passes everything; `Type='Administrator'` passes everything **except** permissions marked `IsDeveloperOnly`. | Schema/platform/ops operations (e.g. table creation, module activation, deployment) stay developer-exclusive. All other gates honour Administrator as a full bypass. |
| Assignment | Role → Permission (no per-user grants) | Roles are already company-scoped via `Role.CompanyId`, so permission grants are implicitly tenant-scoped. Per-user overrides would multiply UI and edge-cases for little gain right now. |
| Migration strategy | **Coexist with `developer` / `admin-or-developer` middleware** | The two legacy middleware keep working unchanged. New routes are protected by a new `permission:<Resource>.<Action>` middleware. Legacy routes can be migrated incrementally; no flag day. |
| Permission catalog scope | **Global, not per-company** | The catalog of "things the system can do" is a property of the codebase, not a tenant. Per-company role rows then pick which permissions they carry. |

---

## Role Type taxonomy

`Role.Type` is constrained to **nine canonical values**. Only the first two have hardcoded effect in the authorization middleware; the rest are decorative for the middleware (they pass or fail solely on their `RolePermission` grants) but carry meaning for downstream data-scope rules.

| # | Type | Display Name | Description | Bypass behaviour | Data scope (downstream, not enforced by this runbook) |
|---|---|---|---|---|---|
| 1 | `Developer` | Developer | The Developer Role is mainly for developers. Developers have access to everything in the system. **Strictly reserved for nSales Technical Staff.** | Bypasses **every** gate, including `IsDeveloperOnly`. | Cross-tenant / platform. |
| 2 | `Administrator` | Administrator | The Administrator Role is for the administrators of a Company Account. Administrators have access to everything in the Company Account. | Bypasses every gate **except** `IsDeveloperOnly`. | Tenant-wide. |
| 3 | `Manager` | Manager | The Manager Role is for the managers in a Company Account. Managers have access to all employee data in the Company Account. | No bypass — capabilities come entirely from `RolePermission`. | Tenant-wide (visibility into all employees' rows). |
| 4 | `Employee` | Employee | The Employee Role is for the employees in a Company Account. Employees have access to their own data in the Company Account. | No bypass. | Self only (own rows). |
| 5 | `Client` | Client | The Client Role is for the members of a Customer. Clients have restricted access to the Company Account. | No bypass. | Restricted to the linked Customer's data. |
| 6 | `Retailer` | Retailer | Role of Retailer. | No bypass. | Retailer-scoped (downstream business rule). |
| 7 | `WebShopViewer` | WebShopViewer | Role of Webshop viewer user. | No bypass. | Storefront-scoped. |
| 8 | `Insights` | Insights | Role for Insights. | No bypass. | Defined by granted permissions. |
| 9 | `Marketing` | Marketing | Role for Marketing. | No bypass. | Defined by granted permissions. |

### Endpoint gating vs data scope

This runbook governs **endpoint** authorization — "can this user call `POST /orders/list`?" The descriptions for Manager / Employee / Client / Retailer / WebShopViewer additionally imply **row-level data scope** — "which rows in the orders table can this user *see* once past the endpoint gate?" Row-level filtering is a downstream concern handled in repository queries and the existing `DataFilter` service. It is **not** in scope for this runbook; introducing the permission layer does not change row-scope behaviour.

### Why only `Developer` and `Administrator` bypass

A Type only earns bypass behaviour if encoding it as data-driven permission grants would be impractical. Both Developer and Administrator are "everything" tiers — encoding them as RolePermission rows is busywork and risks drift between code and data. Every other Type has its capabilities expressed through explicit grants, which makes per-tenant variation possible without code changes.

### DB-level enforcement

`Role.Type` is constrained at the DB level via a MySQL `ENUM(...)` (see the **Schema (already in place)** section near the top for the exact column definition). Application-side validation in the role-edit form additionally restricts the dropdown so admins cannot type free-form values.

### Migration of existing rows

When the ENUM was applied, pre-existing `Role` rows with `Type` values outside the canonical nine were normalised to `'Employee'` while preserving each row's `Name` (so a legacy `'Supervisor'` row keeps "Supervisor" as the display label, just no longer as the Type). This is historical — no further migration is required.

### Seeder defaults

The `RolePermissionBackfillSeeder` (Phase A) bootstraps `RolePermission` rows for the bypass Types only:

- `Type='Developer'` → grant every Permission (including `IsDeveloperOnly=1`).
- `Type='Administrator'` → grant every Permission where `IsDeveloperOnly=0`.
- All other Types → no defaults. Each tenant configures their Manager/Employee/Client/etc. roles via the EditRole UI to match their business workflow.

---

## Permission table schema (as built)

The `Permission` table is in the `NVISION_OFFICE` database with this shape:

| Column | Type | Notes |
|---|---|---|
| `Id` | int PK | |
| `Name` | varchar | The action verb — e.g. `Create`, `Read`, `Update`, `Delete`, `Manage` |
| `Aliases` | varchar | **The slug.** Middleware and frontend compare against this value, e.g. `"Order.Create"`. Convention: `{Module.Name}.{Permission.Name}`. |
| `ApplicationId` | int FK → `Application.Id` | Which application the permission belongs to (e.g. core office vs. webshop). |
| `ModuleId` | int FK → `Module.Id` | Which module the permission applies to. The doc's notion of "Resource" maps to `Module.Name`. |
| `Description` | varchar nullable | Human-readable description for the UI (added by the DDL delta above). |
| `IsDeveloperOnly` | tinyint(1) | `1` = platform-level permission; only Developer Type can ever pass the gate. Administrator bypass does **not** apply. `0` = role-grantable permission; Administrator bypass applies and the permission can be granted to any role via `RolePermission`. Added by the DDL delta above. |
| `InsertTime`, `UpdateTime`, `DeleteTime` | datetime | `BaseModel` conventions. |

Throughout the rest of this runbook, "**slug**" means `Permission.Aliases`. The runbook reads `Resource = Module.Name` and `Action = Permission.Name` when grouping the catalog for the UI grid.

---

## Current implementation status

| Area | Status | Open work |
|---|---|---|
| Schema | ✅ Done | None. `RolePermission` has Id/timestamps/unique; `Permission` has `Description`/`IsDeveloperOnly`; `Role.Type` is ENUM-constrained; `CompanyUserRolePermission` dropped. |
| Phase A — Models + seeders | ⏳ Not started | `Permission` model, `RolePermission` pivot, `Role::permissions()` relation, both seeders. **Catalog is empty** — `PermissionSeeder` is the unblocking step. |
| Phase B — Service/repo/controller/routes | ⏳ Not started | Three endpoints (`/permissions/list`, `/role/permissions`, `/role/permissions/sync`) + supporting classes. |
| Phase C — `UserHasPermission` middleware | ⏳ Not started | New middleware + alias in `bootstrap/app.php`. Legacy `developer` / `admin-or-developer` stay untouched. |
| Phase D — Auth response + frontend wiring | ⏳ Not started | `CompanyService::getAuthUserCompanies` to include `permissions[]`; `authStore`, `companyStore`, `useCheckAccess`, router, `BaseNavigation.vue`. |
| Phase E — `EditRole.vue` permission grid | ⏳ Not started | New grid UI in the existing edit view; `resources/js/models/Office/Permission.js`. |
| Phase F — Cache invalidation + legacy migration | ⏳ Not started | Sync-time cache flush; incremental route migration off `developer` middleware. |

---

## Phase A — Backend foundation (model + pivot + seeders)

**Goal:** populate the (currently empty) `Permission` catalog and wire the PHP model layer (`Permission`, `RolePermission` pivot, `Role::permissions()` relation) so roles can be granted permissions. Schema is already in place — `PermissionSeeder` is the unblocking first step; the model code lands against real rows.

### New files

- `database/seeders/PermissionSeeder.php` — idempotent `upsert` of the canonical permission catalog. Each seeded row is `(Name, Aliases, ApplicationId, ModuleId, Description, IsDeveloperOnly)`. `Aliases` is computed at seed time as `"{Module.Name}.{Name}"`, e.g. the row `Name='Create', ModuleId=<Order>` produces `Aliases='Order.Create'`. The upsert is keyed on `Aliases` so re-runs are no-ops. `IsDeveloperOnly` is `1` for platform/schema/ops modules listed under **Developer-only catalog** below, and `0` for everything else. The initial catalog covers every Resource currently protected by `developer` / `admin-or-developer` middleware, derived from `routes/api.php`. See **Initial catalog** at the bottom of this section.

- `database/seeders/RolePermissionBackfillSeeder.php` — for every existing `Role` with `Type='Developer'`, grant the **full** permission set (all rows, including `IsDeveloperOnly=1`); for every `Role` with `Type='Administrator'`, grant only the role-grantable subset (`IsDeveloperOnly=0`). Developer-only grants on Administrator rows would be misleading because the middleware will refuse them anyway.

  Roles are per-company (`Role.CompanyId` is set), so a tenant with N companies can have N distinct rows of `Type='Developer'`. The backfill iterates **every** Role row with `Type IN ('Developer','Administrator')` and grants the appropriate subset. This keeps bypass roles consistent across tenants without special-casing.

- `app/Models/Office/Permission.php`
  ```php
  class Permission extends BaseModel
  {
      protected $table = 'Permission';

      public function module(): BelongsTo
      {
          return $this->belongsTo(Module::class, 'ModuleId', 'Id');
      }

      public function application(): BelongsTo
      {
          return $this->belongsTo(Application::class, 'ApplicationId', 'Id');
      }

      public function roles(): BelongsToMany
      {
          return $this->belongsToMany(Role::class, 'RolePermission', 'PermissionId', 'RoleId', 'Id', 'Id');
      }
  }
  ```

- `app/Models/Office/RolePermission.php` — pivot, mirrors `CompanyUserRole`.

### Modified files

- `app/Models/Office/Role.php` — add a `permissions()` `BelongsToMany` relation through `RolePermission`.

### `RolePermission` schema (reference)

```
RolePermission
  Id            int PK auto-increment
  RoleId        int FK → Role.Id
  PermissionId   int FK → Permission.Id
  InsertTime, UpdateTime, DeleteTime
  UNIQUE (RoleId, PermissionId)
```

Per `CLAUDE.md`, the central `NVISION_OFFICE` tables (Role, CompanyUser, etc.) are not user-managed schema, so any future changes are run directly against `NVISION_OFFICE` rather than through `TableController`.

### Initial catalog (PermissionSeeder)

Derived from `routes/api.php`. Anything currently behind `developer` middleware becomes a permission (today there are no `admin-or-developer` routes — every gated route is `developer`). The seeder upserts (idempotent) so it can be re-run safely.

**Slug vs. Module taxonomy.** The slug (`Permission.Aliases`) is `{ResourceName}.{Action}` — e.g. `EmailLayout.Create`. `ResourceName` is the public name middleware and the frontend compare against and **may differ** from `Module.Name`. The `Module` column below is the actual `Module.Name` looked up at seed time to set `Permission.ModuleId`; it groups permissions in the management UI grid. For example, `EmailLayout` and `EmailTemplate` both live under `Module.Name = 'Email'`, but their slugs stay `EmailLayout.*` and `EmailTemplate.*`. Each seeder entry is therefore a `(Module, Permissions, Actions)` triple: every `Permission × Action` pair becomes one `Permission` row with `ModuleId` resolved from `Module`.

#### Developer-only catalog (`IsDeveloperOnly = 1`)

Administrator bypass does **not** apply to these. Only roles with `Type='Developer'` ever pass.

| Module (→ Permission.ModuleId) | Permissions (slug prefix) | Actions | Example Aliases (slug) | Rationale |
|---|---|---|---|---|
| `Table` | `Table`, `TableField`, `TableIndex` | Create, Read, Update, Delete, Manage | `Table.Create`, `TableField.Update`, … | Tenant-DB schema operations — mistakes corrupt customer data. |
| `Module` | `Module`, `Application`, `ModulePackage`, `ApplicationModule`, `ModuleSetting` | Create, Read, Update, Delete | `Module.Create`, `ModuleSetting.Update`, … | The system's own catalog of modules/applications — codebase-level, not tenant business data. |
| `Company` | `Company` | Create, Read, Update, Delete | `Company.Create`, `Company.Delete`, … | Tenant provisioning is platform-level. |
| `Email` | `EmailLayout`, `EmailTemplate` | Create, Read, Update, Delete | `EmailLayout.Create`, `EmailTemplate.Update`, … | Global email artifacts — only Developer can author/edit. Tenant-side `CompanyEmail*` variants are role-grantable (next table). |

Platform/ops permissions that have no corresponding `Module` row (`Database`, `ModulePackageModule`, `Deployment`, `Git`, `Horizon`) are **not** seeded today. Developer-Type bypass already protects the underlying routes via `UserIsDeveloper` middleware. They can be added later (with `ModuleId = NULL`, or a new System-level Module row) when those routes migrate to `permission:*` gates in Phase F.

#### Role-grantable catalog (`IsDeveloperOnly = 0`)

Administrator bypass applies; can also be granted explicitly to any custom role via `RolePermission`.

| Module (→ Permission.ModuleId) | Permissions (slug prefix) | Actions | Example Aliases (slug) |
|---|---|---|---|
| `Role` | `Role` | Manage | `Role.Manage` |
| `User` | `User`, `CompanyUser` | Create, Read, Update, Delete | `User.Create`, `CompanyUser.Create`, … |
| `Language` | `Language`, `CompanyLanguage` | Create, Read, Update, Delete | `Language.Read`, `CompanyLanguage.Update`, … |
| `Translation` | `Translation`, `CompanyTranslation` | Create, Read, Update, Delete | `Translation.Create`, `CompanyTranslation.Update`, … |
| `Email` | `EmailConfiguration`, `CompanyEmailLayout`, `CompanyEmailTemplate` | Create, Read, Update, Delete | `EmailConfiguration.Update`, `CompanyEmailLayout.Create`, … |
| `DataFilter` | `DataFilter` | Create, Read, Update, Delete | `DataFilter.Create`, … |
| `Theme` | `Theme` | Create, Read, Update, Delete | `Theme.Create`, … |
| `Order` | `Order`, `OrderLine` | Create, Read, Update, Delete | `Order.Create`, `OrderLine.Update`, … |
| `Customer` | `Customer` | Create, Read, Update, Delete | `Customer.Create`, … |
| `Item` | `Item`, `ItemAttribute` | Create, Read, Update, Delete | `Item.Create`, `ItemAttribute.Update`, … |
| `WSPage` | `WebShopText`, `WebShopPage` | Create, Read, Update, Delete | `WebShopText.Create`, `WebShopPage.Update`, … |
| `WSUser` | `WebShopUser` | Create, Read, Update, Delete | `WebShopUser.Create`, … |

`ApplicationId` is set per row based on which Application the Module belongs to (look up via the existing `ApplicationModule` pivot during seeding). If a Module has no Application linkage today, leave `ApplicationId` null or seed it under the default office Application — the middleware does not consult `ApplicationId` for permission checks.

### Exit criteria

- `Permission` table is reachable as `App\Models\Office\Permission`.
- `RolePermission` exists with the unique index.
- Running both seeders idempotently leaves the row counts unchanged on the second run.
- Each existing Developer role has full permission coverage (including `IsDeveloperOnly=1`). Each existing Administrator role has full coverage of the role-grantable subset only (`IsDeveloperOnly=0`).

---

## Phase B — Backend service, repository, controller, routes

**Goal:** expose permission CRUD for the SPA (list catalog, fetch a role's grants, sync grants).

### New files

- `app/Repositories/Eloquent/Office/Permission/PermissionRepositoryInterface.php`
- `app/Repositories/Eloquent/Office/Permission/PermissionRepository.php` — extends `BaseRepository`.
- `app/Repositories/Eloquent/Office/RolePermission/RolePermissionRepositoryInterface.php`
- `app/Repositories/Eloquent/Office/RolePermission/RolePermissionRepository.php` — exposes `syncForRole(int $roleId, array $permissionIds)` (single transaction, full replace).
- `app/Services/Permission/PermissionServiceInterface.php`
- `app/Services/Permission/PermissionService.php` — methods:
  - `listAll(Request)` → returns the catalog with `module` and `application` eager-loaded so the UI can group rows by `Module.Name`.
  - `getRolePermissions(Request)` → `{ RoleId }` → array of granted permission IDs.
  - `syncRolePermissions(Request)` → `{ RoleId, PermissionIds: int[] }`. Transactional full-replace via `RolePermissionRepository::syncForRole`.
- `app/Http/Controllers/PermissionController.php` — three thin endpoints wrapping the service, returning `ApiResponseTransformer::success`.
- `app/Http/Requests/Permission/ListAll.php`
- `app/Http/Requests/Permission/GetRolePermissions.php` — `RoleId: required|integer|exists:Role,Id`
- `app/Http/Requests/Permission/SyncRolePermissions.php` — `RoleId: required|integer`, `PermissionIds: present|array`, `PermissionIds.*: integer|exists:Permission,Id`

### Modified files

- `app/Providers/ServiceServiceProvider.php` — bind `PermissionServiceInterface` → `PermissionService`.
- `app/Providers/RepositoryServiceProvider.php` — bind both repository interfaces.
- `routes/api.php`, inside the `auth:api` group:

  ```php
  Route::post('/permissions/list',              [PermissionController::class, 'listAll']);
  Route::post('/role/permissions',              [PermissionController::class, 'getRolePermissions']);
  Route::post('/role/permissions/sync',         [PermissionController::class, 'syncRolePermissions'])
       ->middleware('admin-or-developer');     // only Dev/Admin may edit role permissions
  ```

### Exit criteria

- All three endpoints respond correctly to authenticated requests.
- `syncRolePermissions` is atomic — partial failures leave no orphan rows.
- A Feature test creates a role, syncs permissions, fetches them back, and asserts equality.

---

## Phase C — Permission-checking middleware (with Dev/Admin bypass)

**Goal:** introduce `permission:<Resource>.<Action>` middleware. Developer and Administrator roles short-circuit through it unconditionally.

**What the middleware does, by cardinality:**

1. Eager-loads the auth user's `companyUsers.roles.permissions` (1 query, fans across the User → CompanyUser → Role → Permission chain).
2. Builds one cache entry **per CompanyUser**, each containing the union of role Types and the union of permission `Aliases` (slugs) across all roles held in that company. The result is an array of `{ CompanyId, RoleTypes[], Permissions[] }`.
3. Looks up the entry matching `SelectedCompanyId` from the request. If the user is not tagged with that company → 403.
4. **Developer fast path**: if `RoleTypes` contains `Developer`, return `$next($request)` (Developer passes every gate, including developer-only).
5. **Developer-only enforcement**: look up whether the required slug is in the cached set of developer-only slugs (a separate, long-lived cache key `PermissionCatalog-DeveloperOnly`). If yes → 403 (no further checks; Administrator does not bypass developer-only permissions).
6. **Administrator bypass** (for non-dev-only permissions only): if `RoleTypes` contains `Administrator`, return `$next($request)`.
7. **Explicit grant check**: the required slug must be present in `Permissions` for that company. Otherwise → 403.

The same `User → CompanyUser → Role → Permission` traversal is used; nothing assumes single-role or single-company. The developer-only check uses a separate cache key because the catalog of developer-only slugs is global, slow-moving, and shared across all users.

### New file

`app/Http/Middleware/UserHasPermission.php`:

```php
class UserHasPermission
{
    public function handle($request, Closure $next, string $permission)
    {
        if (!$request->has('SelectedCompanyId')) {
            throw new Exception('Selected Company Id (SelectedCompanyId) is needed.', 422);
        }
        $user = Auth::user();
        $selectedCompanyId = (int) $request->input('SelectedCompanyId');

        $cached = Cache::remember("UserCompanyWiseAccess-$user->Id", 60, function () use ($user) {
            $u = User::with(['companyUsers.roles.permissions:Id,Aliases'])
                     ->where('Id', $user->Id)
                     ->first();
            $result = [];
            foreach ($u->companyUsers as $cu) {
                $roleTypes = $cu->roles->pluck('Type')->all();
                // Union: a user with two roles in one company gets the union of their permissions.
                $slugs = $cu->roles->pluck('permissions')->flatten()->pluck('Aliases')->unique()->values()->all();
                $result[] = [
                    'CompanyId'  => $cu->CompanyId,
                    'RoleTypes'  => $roleTypes,
                    'Permissions' => $slugs,
                ];
            }
            return $result;
        });

        $entry = collect($cached)->firstWhere('CompanyId', $selectedCompanyId);
        if (!$entry) {
            return ApiResponseTransformer::error([], 'Access Denied.', 403);
        }

        // 1. Developer fast path — passes everything, including developer-only permissions.
        if (in_array('Developer', $entry['RoleTypes'], true)) {
            return $next($request);
        }

        // 2. Developer-only enforcement — Administrator must NOT bypass these.
        $developerOnly = Cache::remember('PermissionCatalog-DeveloperOnly', 3600, function () {
            return Permission::where('IsDeveloperOnly', 1)->pluck('Aliases')->all();
        });
        if (in_array($permission, $developerOnly, true)) {
            return ApiResponseTransformer::error([], 'Access Denied.', 403);
        }

        // 3. Administrator bypass — applies only to non-developer-only permissions.
        if (in_array('Administrator', $entry['RoleTypes'], true)) {
            return $next($request);
        }

        // 4. Explicit grant check.
        if (!in_array($permission, $entry['Permissions'], true)) {
            return ApiResponseTransformer::error([], 'Access Denied.', 403);
        }
        return $next($request);
    }
}
```

### Register alias

`bootstrap/app.php`:
```php
$middleware->alias([
    'company'            => \App\Http\Middleware\SetCompanyDatabaseConnection::class,
    'developer'          => \App\Http\Middleware\UserIsDeveloper::class,
    'admin-or-developer' => \App\Http\Middleware\UserIsAdminOrDeveloper::class,
    'permission'         => \App\Http\Middleware\UserHasPermission::class,   // NEW
]);
```

### Cache key consolidation

The new `UserCompanyWiseAccess-{UserId}` cache key carries **both** role types and permission slugs (one DB hit serves all checks for 60 s). The existing `UserCompanyWiseRoles-{UserId}` key used by `UserIsDeveloper` / `UserIsAdminOrDeveloper` is left in place — those middleware are not modified. Both keys can coexist; cache eviction concerns are addressed in **Phase F**.

### Usage

```php
Route::post('/orders/list',   [OrderController::class, 'list'])  ->middleware('permission:Order.Read');
Route::post('/order/create',  [OrderController::class, 'create'])->middleware('permission:Order.Create');
Route::post('/order/update',  [OrderController::class, 'update'])->middleware('permission:Order.Update');
Route::post('/order/delete',  [OrderController::class, 'delete'])->middleware('permission:Order.Delete');
```

A Developer or Administrator hitting any of the above passes through without consulting permissions. Anyone else needs the exact slug.

### Helper on the User model

For services that need to check permissions outside the middleware path (e.g. conditional logic inside a controller):

```php
// app/Models/Office/User.php
public function hasPermission(string $slug, int $companyId): bool
{
    $companyUser = $this->companyUsers->firstWhere('CompanyId', $companyId);
    if (!$companyUser) return false;
    $roleTypes = $companyUser->roles->pluck('Type')->all();

    // Developer always passes.
    if (in_array('Developer', $roleTypes, true)) return true;

    // Developer-only permissions block Administrator.
    $isDevOnly = Cache::remember('PermissionCatalog-DeveloperOnly', 3600, function () {
        return Permission::where('IsDeveloperOnly', 1)->pluck('Aliases')->all();
    });
    if (in_array($slug, $isDevOnly, true)) return false;

    // Administrator bypasses non-developer-only permissions.
    if (in_array('Administrator', $roleTypes, true)) return true;

    // Explicit grant.
    return $companyUser->roles
        ->pluck('permissions')
        ->flatten()
        ->contains(fn ($p) => $p->Aliases === $slug);
}
```

### Exit criteria

- A test route protected by `permission:Order.Create` returns 200 for a Developer, 200 for an Administrator, 200 for an Employee whose role has `Order.Create` granted, and 403 for an Employee whose role doesn't have it.
- The 60s cache is populated after the first request and reused on subsequent ones.

---

## Phase D — Frontend integration

**Goal:** the SPA learns which permission slugs the current user holds and gates routes, menu items, and inline UI accordingly. Developer/Administrator users get an implicit pass at every gate.

### Backend change (one line of change to data shape)

`CompanyService::getAuthUserCompanies()` (`app/Services/Company/CompanyService.php`, the method backing `POST /api/companies/auth-user-companies` — the SPA already calls this to discover role Types) currently returns one entry per Company tagging with `roles: [...]` already pulled from `$company->companyUsers[0]->roles` (line 239). Eager-load `companyUsers.roles.permissions` and extend that same row with `permissions: [...]`. The list must match the middleware's effective decisions, so it's computed by role type:

- **Developer role** in the company → `permissions = (all Permission.Aliases)` (every slug, including developer-only ones).
- **Administrator role** in the company → `permissions = (Permission.Aliases where IsDeveloperOnly = 0)` (every role-grantable slug; developer-only ones are excluded so the SPA cannot offer them).
- **Custom role(s)** → `permissions = union of role permissions' Aliases` (explicit grants only; the role-grantable subset is enforced because `RolePermissionBackfillSeeder` never grants developer-only permissions to non-Developer roles).

```json
[
  { "Id": 7,  "Name": "Acme",   "roles": ["Developer"],          "permissions": [] },
  { "Id": 12, "Name": "Globex", "roles": ["Employee"],           "permissions": ["Order.Read","Order.Create"] },
  { "Id": 18, "Name": "Stark",  "roles": ["Employee","Manager"], "permissions": ["Order.Read","Order.Create","User.Read"] }
]
```

The third entry illustrates the **multi-role-in-one-company** case: permission union across all roles assigned to that CompanyUser. Bypass companies can return `permissions: []` — the frontend recomputes bypass from `roles`, so the list is only consulted for non-bypass roles.

No new endpoint. Permissions ride along with roles, on the same response the SPA already consumes during login bootstrap and company switch.

### Frontend changes

- `resources/js/stores/authStore.js` — add:
  - `permissions: []` state
  - `setPermissions(payload)` action
  - `getPermissions` getter
- `resources/js/stores/companyStore.js` — in `setSelectedCompanyById`, after `authStore.setRoles(...)`, also call `authStore.setPermissions(this.selectedCompany.permissions ?? [])`. The selected-company swap path is the **only** place permissions/roles are propagated into `authStore`. The SPA never holds the union across all of the user's companies — it would be wrong (different bypass per company) and unnecessary (only one company is "active" at a time).
- `resources/js/composables/useCheckAccess.js` — extend:
  ```js
  function isBypassRole() {
      const authStore = useAuthStore();
      const roles = authStore.getRoles ?? [];
      return roles.includes('Developer') || roles.includes('Administrator');
  }
  function hasPermission(slug) {
      if (isBypassRole()) return true;
      const authStore = useAuthStore();
      return (authStore.getPermissions ?? []).includes(slug);
  }
  function hasAnyPermission(slugs) {
      if (isBypassRole()) return true;
      return slugs.some(hasPermission);
  }
  async function checkAccess(roles, module = null, permissions = null) {
      // existing roles / module checks ...
      if (permissions && permissions.length && !hasAnyPermission(permissions)) {
          await router.push({ name: 'home' });
          notificationStore.showNotification("Access Denied.", "error");
      }
  }
  return { hasRoleAccess, hasPermission, hasAnyPermission, checkAccess, isBypassRole };
  ```
- `resources/js/router/index.js` — pass `to.meta.permissions` into `checkAccess` alongside `to.meta.roles` and `to.meta.module`.

### Route meta convention going forward

`meta.roles` continues to work for legacy routes. `meta.permissions` is the preferred gate. Both can coexist on a route during migration.

```js
meta: {
    requiresAuth: true,
    requiresCompany: true,
    permissions: ['Order.Read'],   // new — preferred
    module: 'Order',               // unchanged
}
```

### Menu visibility

`resources/js/data/menu.js` — each node may now carry an optional `permissions: ['Order.Read']`. `resources/js/components/BaseNavigation.vue` extends its visibility check:

```vue
<li v-if="(!node.roles || hasRoleAccess(node.roles))
       && (!node.permissions || hasAnyPermission(node.permissions))">
```

`Developer` and `Administrator` users see everything because `hasAnyPermission` short-circuits via `isBypassRole()`.

### Inline UI gates

Buttons and inline UI fragments use the same composable:

```vue
<button v-if="hasPermission('Order.Delete')" @click="onDelete">Delete</button>
```

### Exit criteria

- Logging in as a Developer or Administrator: every existing menu item and route still resolves.
- Logging in as an Employee with no permissions: only routes/menus with no `permissions` constraint are reachable.
- Granting `Order.Read` to the Employee role and refreshing the SPA: the Orders menu and route become reachable.

---

## Phase E — Permission management UI

**Goal:** let an Administrator (or Developer) grant permissions to a non-bypass role.

### Files

- `resources/js/models/Office/Permission.js` — three API methods mirroring the routes from Phase B.
- `resources/js/views/roles/EditRole.vue` — extend the existing edit view with a permissions section.

### UX

When editing a role:

- If the role's `Type` is `Developer`: render a read-only banner explaining that Developer has full access by default and the permission grid is disabled. (Underlying data is still seeded for consistency, just not user-editable here.)
- If the role's `Type` is `Administrator`: render the grid in read-only mode showing all role-grantable permissions (`IsDeveloperOnly = 0`) as "granted by default", and developer-only permissions (`IsDeveloperOnly = 1`) as **locked** with a "Developer only" badge. Administrator cannot toggle either set.
- Otherwise (custom role Type): render an editable grid sourced from `Permission.listAll()` (with `module` eager-loaded). Rows = `Module.Name` (the doc's "Resource"), columns = distinct `Permission.Name` values across the catalog (the doc's "Action": Create/Read/Update/Delete/Manage). Cells = checkboxes bound to `Permission.Id` for that `(Module, Name)` pair.
  - Permission rows where `IsDeveloperOnly = 1` are rendered **disabled with a "Developer only" badge** — a non-developer role cannot be granted them, no matter who's editing. The backend's `SyncRolePermissions` form-request additionally validates that no developer-only permission IDs are submitted for a non-Developer role (defense in depth).
  - Cells where the permission doesn't exist in the catalog show `—`.
- A "Save" button POSTs `{ RoleId, PermissionIds: [...] }` to `/role/permissions/sync`. Use a full-replace strategy (not incremental diff) — simpler, audit-friendly.

Granting permissions to a Role affects **every** CompanyUser that holds that Role within the same company. Because Roles are company-scoped (`Role.CompanyId`), there is no risk of an edit in Company A leaking into Company B — they are separate rows. The UI does not need a company picker for permission editing: it inherits the company context from the Role being edited.

### Exit criteria

- An Administrator can open EditRole for a custom role, toggle checkboxes, save, refresh, and see the selection persist.
- Logging in as a user holding that role reflects the change after the 60 s cache expires (or after an explicit cache clear, see Phase F).

---

## Phase F — Cache invalidation and migration of legacy routes

### Cache invalidation

The 60 s TTL on `UserCompanyWiseAccess-{UserId}` is short enough for normal operation but causes confusing UX during the moment after an admin edits a role's permissions. Two safeguards:

1. In `PermissionService::syncRolePermissions`, after the sync transaction commits, look up every `CompanyUser` attached to that role and forget the cache key for each affected user:
   ```php
   $userIds = CompanyUserRole::where('RoleId', $roleId)
       ->join('CompanyUser', 'CompanyUser.Id', '=', 'CompanyUserRole.CompanyUserId')
       ->pluck('CompanyUser.UserId')->unique();
   foreach ($userIds as $uid) {
       Cache::forget("UserCompanyWiseAccess-$uid");
       Cache::forget("UserCompanyWiseRoles-$uid");   // legacy key
   }
   ```
2. Same flush logic in `UserService::syncCompanyUserRoles` (when a user's role set changes).

The cache key is per-User (not per-(User, Company)) because all of a user's company-wise entries are computed in one DB hit and stored as a single payload. Switching companies on the frontend does not require a cache refresh — the middleware just picks a different entry from the array.

### Incremental migration of legacy routes

With the permission layer live, legacy routes can be moved off `developer` / `admin-or-developer` one at a time. The migration recipe per route:

1. Confirm the equivalent permission exists in `PermissionSeeder` (and is granted to Developer + Administrator by `RolePermissionBackfillSeeder`).
2. Replace `->middleware('developer')` with `->middleware('permission:<Resource>.<Action>')`.
3. Manually verify the route still 200s for Developer and Administrator and 403s for an Employee without the permission.

This is incremental and never has to land in a single PR. Until all routes are migrated, `UserIsDeveloper` / `UserIsAdminOrDeveloper` remain in `bootstrap/app.php`.

### Eventual cleanup (out of scope for the first pass)

Once every route is moved off the two legacy middleware:

- Delete `app/Http/Middleware/UserIsDeveloper.php` and `app/Http/Middleware/UserIsAdminOrDeveloper.php`.
- Drop their aliases from `bootstrap/app.php`.
- Drop the `UserCompanyWiseRoles-*` cache key (the new consolidated key supersedes it).
- Replace the scattered `in_array('Developer', …)` checks in `HorizonServiceProvider`, `RoleService`, `UserService` with `$user->hasPermission(...)` calls or equivalent. (Horizon dashboard becomes `permission:Horizon.Manage`.)

---

## Critical files

**New (backend):**
- `app/Models/Office/Permission.php`
- `app/Models/Office/RolePermission.php`
- `app/Repositories/Eloquent/Office/Permission/{PermissionRepositoryInterface.php,PermissionRepository.php}`
- `app/Repositories/Eloquent/Office/RolePermission/{RolePermissionRepositoryInterface.php,RolePermissionRepository.php}`
- `app/Services/Permission/{PermissionServiceInterface.php,PermissionService.php}`
- `app/Http/Controllers/PermissionController.php`
- `app/Http/Requests/Permission/{ListAll.php,GetRolePermissions.php,SyncRolePermissions.php}`
- `app/Http/Middleware/UserHasPermission.php`
- `database/seeders/PermissionSeeder.php`
- `database/seeders/RolePermissionBackfillSeeder.php`

**Modified (backend):**
- `app/Models/Office/Role.php` — add `permissions()` relation.
- `app/Models/Office/User.php` — add `hasPermission()` helper.
- `app/Providers/{ServiceServiceProvider.php,RepositoryServiceProvider.php}` — register bindings.
- `app/Services/Company/CompanyService.php::getAuthUserCompanies` — include `permissions[]` per company.
- `app/Services/User/UserService.php::syncCompanyUserRoles` — flush access cache after sync.
- `bootstrap/app.php` — register `permission` alias.
- `routes/api.php` — add three permission routes.

**New (frontend):**
- `resources/js/models/Office/Permission.js`
- Permission grid section/component in EditRole.

**Modified (frontend):**
- `resources/js/stores/{authStore.js,companyStore.js}` — permissions state + propagation.
- `resources/js/composables/useCheckAccess.js` — `hasPermission`, `hasAnyPermission`, `isBypassRole`; extend `checkAccess`.
- `resources/js/router/index.js` — pass `meta.permissions` to `checkAccess`.
- `resources/js/components/BaseNavigation.vue` — permission-aware visibility.
- `resources/js/views/roles/EditRole.vue` — grid UI.
- `resources/js/data/menu.js` — add `permissions` to nodes as routes are migrated.

---

## Reuse / no-new-code

- `BaseRepository::paginatedData` for catalog listing if it ever needs pagination.
- `ServiceDto` return contract on every new service method.
- `useGridManagement`, `useFormErrors` composables for the EditRole permission grid.
- The auth response pipeline (`getAuthUserCompanies`) — permissions ride on the same payload as roles. No new auth endpoint.
- The 60 s `Cache::remember(..., 60, ...)` pattern from the existing role middleware — same shape, new key.

## Reuse / what NOT to build

- **No per-user permission overrides.** Decision was roles-only.
- **No new auth/login endpoint** — the SPA already calls `getAuthUserCompanies`, and we extend that response.
- **No changes to** `UserIsDeveloper` or `UserIsAdminOrDeveloper` in this pass. They keep working; routes move off them incrementally in Phase F.
- **No deletion of legacy middleware** until every route is migrated.

---

## Verification

After Phases A–E (Phase F is open-ended, no single bar):

1. **Schema**: `SHOW CREATE TABLE Permission; SHOW CREATE TABLE RolePermission;` — matches the shapes listed in the **Schema (already in place)** section.
2. **Seeder idempotency**: run `php artisan db:seed --class=PermissionSeeder` twice — second run inserts zero new rows. Same for `RolePermissionBackfillSeeder`.
3. **Backend gating**:
   - Create a test route `POST /api/_test/permission-check` protected by `permission:Order.Create`.
   - Hit it as a Developer → 200.
   - Hit it as an Administrator → 200.
   - Hit it as an Employee whose role lacks `Order.Create` → 403.
   - Grant `Order.Create` to that Employee role, flush cache → 200.
4. **Cache**: after step 3, inspect `Cache::get('UserCompanyWiseAccess-{userId}')` — value contains the user's permission slugs.
5. **Sync invalidation**: edit a role's permissions through the SPA; immediately re-fetch as an affected user; behavior reflects the change without waiting 60 s.
6. **Auth response**: `POST /api/companies/auth-user-companies` returns `permissions: [...]` per company alongside `roles: [...]`.
7. **Frontend**:
   - Log in as Developer/Administrator: every menu item and route resolves.
   - Log in as Employee with no permissions: protected items disappear from menus; deep-linking to a protected route redirects to home with the "Access Denied." notification.
   - Grant `Order.Read` to that Employee role and reload: Orders menu and route become reachable.
8. **Backwards compatibility**: every route still protected by `developer` / `admin-or-developer` keeps behaving exactly as before.
9. **Multi-role union (single company)**: create a user, give them two non-bypass roles in the same company — Role X with `Order.Read`, Role Y with `Order.Create`. Hit `POST /api/companies/auth-user-companies`; assert that company's `permissions` contains both slugs (no duplicates). Hit both protected endpoints; both return 200.
10. **Cross-company isolation**: tag a user as Developer in Company A and Employee (no permissions) in Company B. With `SelectedCompanyId = A`, every `permission:*` route 200s (bypass). With `SelectedCompanyId = B`, the same routes 403. Switching companies in the SPA flips the menu and route gates immediately.
11. **Bypass with mixed roles in one company**: a user with Roles `Employee` + `Developer` in the same company should bypass — the Developer fast-path matches first.
12. **Developer-only enforcement**: protect a test route by `permission:Table.Create` (a developer-only slug). Confirm:
    - Developer → 200.
    - Administrator → **403** (Administrator does NOT bypass developer-only permissions).
    - Custom role with `Table.Create` somehow granted → 403 anyway (the developer-only gate is checked before the explicit-grant gate; the `SyncRolePermissions` validator also refuses such grants for non-Developer roles).
13. **Developer-only catalog cache**: clear `PermissionCatalog-DeveloperOnly`, hit a protected developer-only route, and confirm the cache is populated and subsequent requests hit it (one DB query per hour, not per request).
14. **Auth response shape**: for a user holding Administrator in company A, the `permissions` array on that company entry contains only role-grantable slugs (no `Table.Create`, no `Module.Create`, etc.). For the same user holding Developer in company B, the array includes those developer-only slugs.
