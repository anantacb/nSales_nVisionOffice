# Role-permission access control — implementation runbook

## Context

The current authorization model is **role-type-based**: capabilities are implied by the literal value of `Role.Type` (`"Developer"`, `"Administrator"`), and checks are scattered across two near-identical middleware classes (`UserIsDeveloper`, `UserIsAdminOrDeveloper`), one Horizon gate, and a handful of `in_array('Developer', …)` guards inside services. Custom role Types carry zero implicit capabilities, so business-specific roles like `"Supervisor"` or `"Salesperson"` are unable to gate any backend action without code changes.

A `Privilege` table now exists in the central `NVISION_OFFICE` database. The goal of this runbook is to wire that table into a working **permission system** with one clear rule:

> **Developer and Administrator role-Types skip permission checks entirely.** Every other role must hold the matching privilege to access a feature. Backend, frontend, menus, and route guards all follow this rule.

For background on how the existing role system works today (data model, services, middleware, frontend stores), see `docs/role-system.md`.

---

## Data model & cardinality

The permission system must respect the existing relations:

```
User  (1) ──< CompanyUser  (N per User, one per tagged Company)
                 │
                 └──< Role  (N per CompanyUser, via CompanyUserRole pivot)
                           │
                           └──< Privilege  (N per Role, via RolePrivilege pivot — new)
```

**What this means operationally:**

- A User is tagged with **one or more Companies** through `CompanyUser`. Each tagging is independent — a user can be a Developer in Company A and an Employee in Company B.
- Within a single Company tagging, a User can hold **one or more Roles** simultaneously. The effective capability is the **union** of all those roles' privileges and role-Types.
- **Bypass triggers per-company**: if *any* of the user's roles in the currently selected company has `Type IN ('Developer', 'Administrator')`, permission checks are skipped for that company. The bypass does *not* carry across companies.
- The active-company permission/role set in the SPA always reflects the **currently selected company**, not the union across all of the user's companies. Switching companies swaps both arrays.

Reference: `app/Models/Office/{User,CompanyUser,CompanyUserRole,Role}.php`.

---

## Decisions

| Decision | Choice | Rationale |
|---|---|---|
| Authorization unit | **Privilege**, a `(Resource, Action)` pair (e.g. `Order.Create`) | Maps to existing Module/Table taxonomy; uniform for both UI and backend gating. |
| Bypass roles | `Type IN ('Developer', 'Administrator')` skip all checks | Matches current behavior — these roles already see everything. No "Administrator with limits" state exists today and we don't want to introduce one in this pass. |
| Assignment | Role → Privilege (no per-user grants) | Roles are already company-scoped via `Role.CompanyId`, so privilege grants are implicitly tenant-scoped. Per-user overrides would multiply UI and edge-cases for little gain right now. |
| Migration strategy | **Coexist with `developer` / `admin-or-developer` middleware** | The two legacy middleware keep working unchanged. New routes are protected by a new `permission:<Resource>.<Action>` middleware. Legacy routes can be migrated incrementally; no flag day. |
| Privilege catalog scope | **Global, not per-company** | The catalog of "things the system can do" is a property of the codebase, not a tenant. Per-company role rows then pick which privileges they carry. |

---

## Assumed Privilege table schema

The runbook below assumes the existing `Privilege` table has the columns listed here. **Confirm or adjust before starting Phase A** — the schema determines what the model and seeder look like.

| Column | Type | Notes |
|---|---|---|
| `Id` | int PK | |
| `Resource` | varchar | e.g. `Order`, `Role`, `Module`, `Translation` |
| `Action` | varchar | e.g. `Create`, `Read`, `Update`, `Delete`, `Manage` |
| `Slug` | varchar UNIQUE | Concatenation `{Resource}.{Action}` — the value middleware and frontend compare against |
| `Description` | varchar nullable | Human-readable description for the UI |
| `InsertTime`, `UpdateTime`, `DeleteTime` | datetime | `BaseModel` conventions |

A `UNIQUE (Resource, Action)` index is expected.

If the existing table uses different column names (e.g. `Name` instead of `Slug`, or no `Slug` at all), the model in Phase A and all permission-string comparisons get adjusted accordingly. Everything else in the runbook is unaffected.

---

## Phase A — Backend foundation (model + pivot + seeders)

**Goal:** make the `Privilege` table accessible from PHP and stand up the `RolePrivilege` pivot so roles can be granted privileges.

### New files

- `app/Models/Office/Privilege.php`
  ```php
  class Privilege extends BaseModel
  {
      protected $table = 'Privilege';

      public function roles(): BelongsToMany
      {
          return $this->belongsToMany(Role::class, 'RolePrivilege', 'PrivilegeId', 'RoleId', 'Id', 'Id');
      }
  }
  ```

- `app/Models/Office/RolePrivilege.php` — pivot, mirrors `CompanyUserRole`.

- `database/seeders/PrivilegeSeeder.php` — idempotent `upsert` of the canonical privilege catalog (Resource, Action, Slug, Description). The initial catalog covers every Resource currently protected by `developer` / `admin-or-developer` middleware, derived from `routes/api.php`. See **Initial catalog** at the bottom of this section.

- `database/seeders/RolePrivilegeBackfillSeeder.php` — for every existing `Role` with `Type='Developer'` or `Type='Administrator'`, grant the full privilege set so the bypass logic in Phase C is purely a fast path (the underlying grants are still there for consistency and for any future "Administrator with limits" use case).

  Roles are per-company (`Role.CompanyId` is set), so a tenant with N companies can have N distinct rows of `Type='Developer'`. The backfill iterates **every** Role row with `Type IN ('Developer','Administrator')` and grants the full privilege catalog, regardless of which company it belongs to. This keeps bypass roles consistent across tenants without special-casing.

### Modified files

- `app/Models/Office/Role.php` — add a `privileges()` `BelongsToMany` relation through `RolePrivilege`.

### New `RolePrivilege` schema

```
RolePrivilege
  Id            int PK
  RoleId        int FK → Role.Id
  PrivilegeId   int FK → Privilege.Id
  InsertTime, UpdateTime, DeleteTime
  UNIQUE (RoleId, PrivilegeId)
```

Per `CLAUDE.md`, tenant schema goes through `TableController`. The central `NVISION_OFFICE` tables (Role, CompanyUser, etc.) are not user-managed schema, so add `RolePrivilege` directly via SQL against `NVISION_OFFICE` (the simplest, most consistent path with how `Role`/`CompanyUserRole` were created). Document the DDL in `docs/role-permission-implementation.md` (this file) so the production deployment can mirror it.

### DDL

```sql
CREATE TABLE `RolePrivilege` (
  `Id`            INT          NOT NULL AUTO_INCREMENT,
  `RoleId`        INT          NOT NULL,
  `PrivilegeId`   INT          NOT NULL,
  `InsertTime`    DATETIME     NULL,
  `UpdateTime`    DATETIME     NULL,
  `DeleteTime`    DATETIME     NULL,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `uq_RolePrivilege_RoleId_PrivilegeId` (`RoleId`, `PrivilegeId`),
  KEY `ix_RolePrivilege_RoleId` (`RoleId`),
  KEY `ix_RolePrivilege_PrivilegeId` (`PrivilegeId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Initial catalog (PrivilegeSeeder)

Derived from `routes/api.php`. Anything currently behind `developer` or `admin-or-developer` becomes a privilege. The seeder upserts (idempotent) so it can be re-run safely.

| Resource | Actions |
|---|---|
| `Role` | Manage |
| `Company` | Create, Read, Update, Delete |
| `User`, `CompanyUser` | Create, Read, Update, Delete |
| `Module`, `Application`, `ModulePackage`, `ApplicationModule`, `ModulePackageModule` | Create, Read, Update, Delete |
| `Table`, `TableField`, `TableIndex` | Create, Read, Update, Delete |
| `Translation`, `Language`, `CompanyLanguage`, `CompanyTranslation` | Create, Read, Update, Delete |
| `EmailLayout`, `EmailTemplate`, `EmailConfiguration`, `CompanyEmailLayout`, `CompanyEmailTemplate` | Create, Read, Update, Delete |
| `DataFilter`, `ModuleSetting`, `Theme` | Create, Read, Update, Delete |
| `Order`, `OrderLine`, `Customer`, `Item`, `ItemAttribute` | Create, Read, Update, Delete |
| `WebShopText`, `WebShopPage`, `WebShopUser` | Create, Read, Update, Delete |
| `Deployment`, `Database`, `Git`, `Horizon` | Manage |

### Exit criteria

- `Privilege` table is reachable as `App\Models\Office\Privilege`.
- `RolePrivilege` exists with the unique index.
- Running both seeders idempotently leaves the row counts unchanged on the second run.
- Each existing Developer/Administrator role has full privilege coverage.

---

## Phase B — Backend service, repository, controller, routes

**Goal:** expose privilege CRUD for the SPA (list catalog, fetch a role's grants, sync grants).

### New files

- `app/Repositories/Eloquent/Office/Privilege/PrivilegeRepositoryInterface.php`
- `app/Repositories/Eloquent/Office/Privilege/PrivilegeRepository.php` — extends `BaseRepository`.
- `app/Repositories/Eloquent/Office/RolePrivilege/RolePrivilegeRepositoryInterface.php`
- `app/Repositories/Eloquent/Office/RolePrivilege/RolePrivilegeRepository.php` — exposes `syncForRole(int $roleId, array $privilegeIds)` (single transaction, full replace).
- `app/Services/Privilege/PrivilegeServiceInterface.php`
- `app/Services/Privilege/PrivilegeService.php` — methods:
  - `listAll(Request)` → returns catalog grouped by Resource for the UI grid.
  - `getRolePrivileges(Request)` → `{ RoleId }` → array of granted privilege IDs.
  - `syncRolePrivileges(Request)` → `{ RoleId, PrivilegeIds: int[] }`. Transactional full-replace via `RolePrivilegeRepository::syncForRole`.
- `app/Http/Controllers/PrivilegeController.php` — three thin endpoints wrapping the service, returning `ApiResponseTransformer::success`.
- `app/Http/Requests/Privilege/ListAll.php`
- `app/Http/Requests/Privilege/GetRolePrivileges.php` — `RoleId: required|integer|exists:Role,Id`
- `app/Http/Requests/Privilege/SyncRolePrivileges.php` — `RoleId: required|integer`, `PrivilegeIds: present|array`, `PrivilegeIds.*: integer|exists:Privilege,Id`

### Modified files

- `app/Providers/ServiceServiceProvider.php` — bind `PrivilegeServiceInterface` → `PrivilegeService`.
- `app/Providers/RepositoryServiceProvider.php` — bind both repository interfaces.
- `routes/api.php`, inside the `auth:api` group:

  ```php
  Route::post('/privileges/list',              [PrivilegeController::class, 'listAll']);
  Route::post('/role/privileges',              [PrivilegeController::class, 'getRolePrivileges']);
  Route::post('/role/privileges/sync',         [PrivilegeController::class, 'syncRolePrivileges'])
       ->middleware('admin-or-developer');     // only Dev/Admin may edit role privileges
  ```

### Exit criteria

- All three endpoints respond correctly to authenticated requests.
- `syncRolePrivileges` is atomic — partial failures leave no orphan rows.
- A Feature test creates a role, syncs privileges, fetches them back, and asserts equality.

---

## Phase C — Permission-checking middleware (with Dev/Admin bypass)

**Goal:** introduce `permission:<Resource>.<Action>` middleware. Developer and Administrator roles short-circuit through it unconditionally.

**What the middleware does, by cardinality:**

1. Eager-loads the auth user's `companyUsers.roles.privileges` (1 query, fans across the User → CompanyUser → Role → Privilege chain).
2. Builds one cache entry **per CompanyUser**, each containing the union of role Types and the union of privilege Slugs across all roles held in that company. The result is an array of `{ CompanyId, RoleTypes[], Privileges[] }`.
3. Looks up the entry matching `SelectedCompanyId` from the request. If the user is not tagged with that company → 403.
4. If **any** of `RoleTypes` is `Developer` or `Administrator` → bypass, return `$next($request)`. (One Developer role is enough — `array_intersect` short-circuits on first overlap.)
5. Otherwise, the required permission slug must be present in `Privileges` for that company.

The same `User → CompanyUser → Role → Privilege` traversal is used; nothing assumes single-role or single-company.

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
            $u = User::with(['companyUsers.roles.privileges:Id,Slug'])
                     ->where('Id', $user->Id)
                     ->first();
            $result = [];
            foreach ($u->companyUsers as $cu) {
                $roleTypes = $cu->roles->pluck('Type')->all();
                // Union: a user with two roles in one company gets the union of their privileges.
                $slugs = $cu->roles->pluck('privileges')->flatten()->pluck('Slug')->unique()->values()->all();
                $result[] = [
                    'CompanyId'  => $cu->CompanyId,
                    'RoleTypes'  => $roleTypes,
                    'Privileges' => $slugs,
                ];
            }
            return $result;
        });

        $entry = collect($cached)->firstWhere('CompanyId', $selectedCompanyId);
        if (!$entry) {
            return ApiResponseTransformer::error([], 'Access Denied.', 403);
        }

        // BYPASS: Developer or Administrator skip permission checks entirely
        if (array_intersect($entry['RoleTypes'], ['Developer', 'Administrator'])) {
            return $next($request);
        }

        if (!in_array($permission, $entry['Privileges'], true)) {
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

The new `UserCompanyWiseAccess-{UserId}` cache key carries **both** role types and privilege slugs (one DB hit serves all checks for 60 s). The existing `UserCompanyWiseRoles-{UserId}` key used by `UserIsDeveloper` / `UserIsAdminOrDeveloper` is left in place — those middleware are not modified. Both keys can coexist; cache eviction concerns are addressed in **Phase F**.

### Usage

```php
Route::post('/orders/list',   [OrderController::class, 'list'])  ->middleware('permission:Order.Read');
Route::post('/order/create',  [OrderController::class, 'create'])->middleware('permission:Order.Create');
Route::post('/order/update',  [OrderController::class, 'update'])->middleware('permission:Order.Update');
Route::post('/order/delete',  [OrderController::class, 'delete'])->middleware('permission:Order.Delete');
```

A Developer or Administrator hitting any of the above passes through without consulting privileges. Anyone else needs the exact slug.

### Helper on the User model

For services that need to check permissions outside the middleware path (e.g. conditional logic inside a controller):

```php
// app/Models/Office/User.php
public function hasPermission(string $slug, int $companyId): bool
{
    $companyUser = $this->companyUsers->firstWhere('CompanyId', $companyId);
    if (!$companyUser) return false;
    $roleTypes = $companyUser->roles->pluck('Type')->all();
    if (array_intersect($roleTypes, ['Developer', 'Administrator'])) return true;
    return $companyUser->roles
        ->pluck('privileges')
        ->flatten()
        ->contains(fn ($p) => $p->Slug === $slug);
}
```

### Exit criteria

- A test route protected by `permission:Order.Create` returns 200 for a Developer, 200 for an Administrator, 200 for an Employee whose role has `Order.Create` granted, and 403 for an Employee whose role doesn't have it.
- The 60s cache is populated after the first request and reused on subsequent ones.

---

## Phase D — Frontend integration

**Goal:** the SPA learns which permission slugs the current user holds and gates routes, menu items, and inline UI accordingly. Developer/Administrator users get an implicit pass at every gate.

### Backend change (one line of change to data shape)

`CompanyService::getAuthUserCompanies()` (`app/Services/Company/CompanyService.php`, the method backing `POST /api/companies/auth-user-companies` — the SPA already calls this to discover role Types) currently returns one entry per Company tagging with `roles: [...]` already pulled from `$company->companyUsers[0]->roles` (line 239). Eager-load `companyUsers.roles.privileges` and extend that same row with `permissions: [...]`, plucked from `companyUsers[0]->roles->pluck('privileges')->flatten()->pluck('Slug')->unique()->values()`:

```json
[
  { "Id": 7,  "Name": "Acme",   "roles": ["Developer"],          "permissions": [] },
  { "Id": 12, "Name": "Globex", "roles": ["Employee"],           "permissions": ["Order.Read","Order.Create"] },
  { "Id": 18, "Name": "Stark",  "roles": ["Employee","Manager"], "permissions": ["Order.Read","Order.Create","User.Read"] }
]
```

The third entry illustrates the **multi-role-in-one-company** case: privilege union across all roles assigned to that CompanyUser. Bypass companies can return `permissions: []` — the frontend recomputes bypass from `roles`, so the list is only consulted for non-bypass roles.

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
- Logging in as an Employee with no privileges: only routes/menus with no `permissions` constraint are reachable.
- Granting `Order.Read` to the Employee role and refreshing the SPA: the Orders menu and route become reachable.

---

## Phase E — Privilege management UI

**Goal:** let an Administrator (or Developer) grant privileges to a non-bypass role.

### Files

- `resources/js/models/Office/Privilege.js` — three API methods mirroring the routes from Phase B.
- `resources/js/views/roles/EditRole.vue` — extend the existing edit view with a permissions section.

### UX

When editing a role:

- If the role's `Type` is `Developer` or `Administrator`: render a read-only banner explaining that this role has full access by default and the permission grid is disabled. (Underlying data is still seeded for consistency, just not user-editable here.)
- Otherwise: render a grid sourced from `Privilege.listAll()`. Rows = Resources, columns = Actions (Create/Read/Update/Delete/Manage), cells = checkboxes. Cells where the (Resource, Action) doesn't exist in the catalog show `—`.
- A "Save" button POSTs `{ RoleId, PrivilegeIds: [...] }` to `/role/privileges/sync`. Use a full-replace strategy (not incremental diff) — simpler, audit-friendly.

Granting privileges to a Role affects **every** CompanyUser that holds that Role within the same company. Because Roles are company-scoped (`Role.CompanyId`), there is no risk of an edit in Company A leaking into Company B — they are separate rows. The UI does not need a company picker for privilege editing: it inherits the company context from the Role being edited.

### Exit criteria

- An Administrator can open EditRole for a custom role, toggle checkboxes, save, refresh, and see the selection persist.
- Logging in as a user holding that role reflects the change after the 60 s cache expires (or after an explicit cache clear, see Phase F).

---

## Phase F — Cache invalidation and migration of legacy routes

### Cache invalidation

The 60 s TTL on `UserCompanyWiseAccess-{UserId}` is short enough for normal operation but causes confusing UX during the moment after an admin edits a role's privileges. Two safeguards:

1. In `PrivilegeService::syncRolePrivileges`, after the sync transaction commits, look up every `CompanyUser` attached to that role and forget the cache key for each affected user:
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

With the privilege layer live, legacy routes can be moved off `developer` / `admin-or-developer` one at a time. The migration recipe per route:

1. Confirm the equivalent privilege exists in `PrivilegeSeeder` (and is granted to Developer + Administrator by `RolePrivilegeBackfillSeeder`).
2. Replace `->middleware('developer')` with `->middleware('permission:<Resource>.<Action>')`.
3. Manually verify the route still 200s for Developer and Administrator and 403s for an Employee without the privilege.

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
- `app/Models/Office/Privilege.php`
- `app/Models/Office/RolePrivilege.php`
- `app/Repositories/Eloquent/Office/Privilege/{PrivilegeRepositoryInterface.php,PrivilegeRepository.php}`
- `app/Repositories/Eloquent/Office/RolePrivilege/{RolePrivilegeRepositoryInterface.php,RolePrivilegeRepository.php}`
- `app/Services/Privilege/{PrivilegeServiceInterface.php,PrivilegeService.php}`
- `app/Http/Controllers/PrivilegeController.php`
- `app/Http/Requests/Privilege/{ListAll.php,GetRolePrivileges.php,SyncRolePrivileges.php}`
- `app/Http/Middleware/UserHasPermission.php`
- `database/seeders/PrivilegeSeeder.php`
- `database/seeders/RolePrivilegeBackfillSeeder.php`

**Modified (backend):**
- `app/Models/Office/Role.php` — add `privileges()` relation.
- `app/Models/Office/User.php` — add `hasPermission()` helper.
- `app/Providers/{ServiceServiceProvider.php,RepositoryServiceProvider.php}` — register bindings.
- `app/Services/Company/CompanyService.php::getAuthUserCompanies` — include `permissions[]` per company.
- `app/Services/User/UserService.php::syncCompanyUserRoles` — flush access cache after sync.
- `bootstrap/app.php` — register `permission` alias.
- `routes/api.php` — add three privilege routes.

**New (frontend):**
- `resources/js/models/Office/Privilege.js`
- Privilege grid section/component in EditRole.

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

- **No per-user privilege overrides.** Decision was roles-only.
- **No new auth/login endpoint** — the SPA already calls `getAuthUserCompanies`, and we extend that response.
- **No changes to** `UserIsDeveloper` or `UserIsAdminOrDeveloper` in this pass. They keep working; routes move off them incrementally in Phase F.
- **No deletion of legacy middleware** until every route is migrated.

---

## Verification

After Phases A–E (Phase F is open-ended, no single bar):

1. **Schema**: `SHOW CREATE TABLE Privilege; SHOW CREATE TABLE RolePrivilege;` — matches the DDL above.
2. **Seeder idempotency**: run `php artisan db:seed --class=PrivilegeSeeder` twice — second run inserts zero new rows. Same for `RolePrivilegeBackfillSeeder`.
3. **Backend gating**:
   - Create a test route `POST /api/_test/permission-check` protected by `permission:Order.Create`.
   - Hit it as a Developer → 200.
   - Hit it as an Administrator → 200.
   - Hit it as an Employee whose role lacks `Order.Create` → 403.
   - Grant `Order.Create` to that Employee role, flush cache → 200.
4. **Cache**: after step 3, inspect `Cache::get('UserCompanyWiseAccess-{userId}')` — value contains the user's privilege slugs.
5. **Sync invalidation**: edit a role's privileges through the SPA; immediately re-fetch as an affected user; behavior reflects the change without waiting 60 s.
6. **Auth response**: `POST /api/companies/auth-user-companies` returns `permissions: [...]` per company alongside `roles: [...]`.
7. **Frontend**:
   - Log in as Developer/Administrator: every menu item and route resolves.
   - Log in as Employee with no privileges: protected items disappear from menus; deep-linking to a protected route redirects to home with the "Access Denied." notification.
   - Grant `Order.Read` to that Employee role and reload: Orders menu and route become reachable.
8. **Backwards compatibility**: every route still protected by `developer` / `admin-or-developer` keeps behaving exactly as before.
9. **Multi-role union (single company)**: create a user, give them two non-bypass roles in the same company — Role X with `Order.Read`, Role Y with `Order.Create`. Hit `POST /api/companies/auth-user-companies`; assert that company's `permissions` contains both slugs (no duplicates). Hit both protected endpoints; both return 200.
10. **Cross-company isolation**: tag a user as Developer in Company A and Employee (no privileges) in Company B. With `SelectedCompanyId = A`, every `permission:*` route 200s (bypass). With `SelectedCompanyId = B`, the same routes 403. Switching companies in the SPA flips the menu and route gates immediately.
11. **Bypass with mixed roles in one company**: a user with Roles `Employee` + `Developer` in the same company should bypass — `array_intersect(['Employee','Developer'], ['Developer','Administrator'])` is non-empty.
