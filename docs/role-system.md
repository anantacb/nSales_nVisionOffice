# Role system — architectural reference

## Context

This document describes how the **Role** concept currently works across the nVisionOffice backend and SPA, end to end. It is intended as the source-of-truth reference for anyone adding new role-gated functionality, debugging an authorization issue, or — as is the next planned change — layering a Privilege/Permission system on top of the existing roles.

There are no implementation steps in this document. For the runbook that builds a Privilege layer on top of what is described here, see `docs/role-privilege-implementation.md` (when added).

---

## Verified current state

| Aspect | Reality |
|---|---|
| Authorization model | Role-*type*-based. Hard-coded checks against role *Type* strings (`"Developer"`, `"Administrator"`). |
| Granularity | All-or-nothing per role type. No per-action / per-resource permissions. |
| Tenant scoping | `Role.CompanyId` — every role belongs to exactly one company. Roles do not cross tenants. |
| Storage | Central `NVISION_OFFICE` database. Roles are NOT in tenant (`mysql_company`) databases. |
| Multi-role per user | Yes. A `CompanyUser` may carry multiple `CompanyUserRole` rows. |
| Custom roles | Yes — anyone can create a Role with a custom `Type`, but custom types carry no automatic capabilities (no code path checks for them). |
| Privilege/permission concept | **None.** No `Privilege` or `Permission` model, table, or check anywhere in `app/`. |
| Frontend cache | `authStore.roles` holds the array of role *Type* strings for the currently selected company. Refreshed when company is switched. |
| Backend cache | `Cache::remember("UserCompanyWiseRoles-{UserId}", 60, …)` in `UserIsDeveloper` and `UserIsAdminOrDeveloper`. |

---

## 1. Overview

The role chain spans four tables, all in the central office database:

```
User                  central, one row per human
  └── CompanyUser     central, one row per (User × Company) membership
        └── CompanyUserRole   central pivot (CompanyUserId → RoleId), allows N roles per membership
              └── Role        central, scoped by CompanyId; carries Name, Type, Description
```

A user accessing tenant X always does so as the union of all roles attached to their `CompanyUser` row for company X. The same human visiting tenant Y is a different `CompanyUser` with a different role set.

Authorization decisions today are made by checking whether the **Type** column on those roles contains a hard-coded string (`"Developer"` or `"Administrator"`). The Type column is the only field consulted; `Name` and `Description` are display-only.

---

## 2. Data model

The project uses dynamic table management (see `CLAUDE.md`) for tenant-DB schema, so there are no Laravel migrations defining these central tables. The columns below are derived from the models and the central template database.

### `Role`
- `Id` (PK, int)
- `CompanyId` (FK → `Company.Id`)
- `Name` (string) — display name, unique within a company
- `Type` (string) — capability marker. Conventional values: `"Developer"`, `"Administrator"`, plus free-form business types (e.g. `"Employee"`, `"Supervisor"`)
- `Description` (nullable string)
- `InsertTime`, `UpdateTime`, `DeleteTime` (inherited from `BaseModel`)

### `CompanyUser`
- `Id` (PK)
- `UserId` (FK → `User.Id`)
- `CompanyId` (FK → `Company.Id`)
- Other membership-level fields (initials, status, etc.)
- `InsertTime`, `UpdateTime`, `DeleteTime`

### `CompanyUserRole` (pivot)
- `Id` (PK)
- `CompanyUserId` (FK → `CompanyUser.Id`)
- `RoleId` (FK → `Role.Id`)
- `InsertTime`, `UpdateTime`, `DeleteTime`

### `User`
- `Id` (PK)
- `Email`, `Hash`, `Salt`, `Disabled`, `IsLocked`, etc. (auth fields hidden in serialization)
- `InsertTime`, `UpdateTime`, `DeleteTime`

---

## 3. Models & relationships

### `app/Models/Office/Role.php`

```php
class Role extends BaseModel
{
    protected $table = 'Role';

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'CompanyId', 'Id');
    }
}
```

Note: `Role` currently has no reverse relationship back to `CompanyUserRole` or `CompanyUser`. Lookups go the other direction.

### `app/Models/Office/CompanyUser.php`

```php
public function roles(): BelongsToMany
{
    return $this->belongsToMany(Role::class, 'CompanyUserRole', 'CompanyUserId', 'RoleId', 'Id', 'Id');
}

public function companyUserRoles(): HasMany
{
    return $this->hasMany(CompanyUserRole::class, 'CompanyUserId', 'Id');
}
```

### `app/Models/Office/CompanyUserRole.php`

```php
public function role(): HasOne
{
    return $this->hasOne(Role::class, 'Id', 'RoleId');
}
```

### `app/Models/Office/User.php`

```php
public function companyUsers(): HasMany
{
    return $this->hasMany(CompanyUser::class, 'UserId', 'Id');
}

public function companyUser(): HasOne   // first CompanyUser only; convenience accessor
{
    return $this->hasOne(CompanyUser::class, 'UserId', 'Id');
}
```

Roles for a given user in a given company are reached as:
```php
$user->companyUsers->firstWhere('CompanyId', $companyId)->roles
```

---

## 4. Service layer — `RoleService`

`app/Services/Role/RoleService.php` exposes the role CRUD via `RoleServiceInterface`. All methods return a `ServiceDto`.

| Method | Payload | Behavior |
|---|---|---|
| `getAssignableRolesByCompany(Request)` | `{ CompanyId, WithDeveloper: bool }` | Lists company's roles. When `WithDeveloper=false` (default), excludes `Type='Developer'` from results — used when assigning roles to regular users in the SPA. |
| `getCompanyRoles(Request)` | Full `paginatedData` payload (filters/search/order/pagination) | Paginated list for the role admin grid. Always company-scoped. |
| `create(Request)` | `{ CompanyId, Name, Type, Description }` | Inserts a new `Role` row. |
| `update(Request)` | `{ Id, Name, Description }` | Updates **Name and Description only**. `Type` is intentionally immutable after creation. |
| `delete(Request)` | `{ RoleId }` | Soft-deletes via `findByIdAndDelete`. |
| `details(Request)` | `{ RoleId }` | Returns role with `company` relation eager-loaded (`Id`, `Name`, `CompanyName`). |

---

## 5. Repository layer

`app/Repositories/Eloquent/Office/Role/RoleRepository.php` extends `BaseRepository` for the standard query plumbing, plus one tenant-aware override:

- `paginatedDataCompanyWise(array $request)` — identical to `BaseRepository::paginatedData()` but injects `$query->where('CompanyId', $request['CompanyId'])` so role listings never accidentally cross tenants.

The `paginatedData` payload contract used here is documented in `RoleRepositoryInterface.php` and matches the rest of the codebase: `selected_columns`, `relations`, `filters`, `filter_by_relation`, `search`, `order`, `pagination`.

---

## 6. Routes

All role endpoints live under `routes/api.php`, inside the `auth:api` JWT-protected group.

| Method | Path | Controller method | Validator | Auth |
|---|---|---|---|---|
| POST | `/api/roles/by-company` | `RoleController::getRolesByCompany` | `Role/GetRoles` | `auth:api` |
| POST | `/api/roles/company-roles` | `RoleController::getCompanyRoles` | `PaginatedDataRequest` | `auth:api` |
| POST | `/api/role/create` | `RoleController::create` | `Role/Create` | `auth:api` |
| POST | `/api/role/update` | `RoleController::update` | `Role/Update` | `auth:api` |
| POST | `/api/role/delete` | `RoleController::delete` | `Role/DetailsOrDelete` | `auth:api` |
| POST | `/api/role/details` | `RoleController::details` | `Role/DetailsOrDelete` | `auth:api` |

**Notable gap:** none of these are protected by the `developer` or `admin-or-developer` middleware. Any authenticated user with the right `CompanyId` payload can call them today.

---

## 7. Authorization enforcement (backend)

Authorization is enforced in three places, all by **hard-coded role-Type string comparison**.

### 7.1 `app/Http/Middleware/UserIsDeveloper.php` (alias: `developer`)

```php
if (!$request->has('SelectedCompanyId')) {
    throw new Exception('Selected Company Id (SelectedCompanyId) is needed.', 422);
}
$user = Auth::user();
$userCompanyWiseRoles = Cache::remember("UserCompanyWiseRoles-$user->Id", 60, function () use ($user) {
    $userWithRoles = User::with(['companyUsers.roles'])->where('Id', $user->Id)->first();
    $userCompanyWiseRoles = [];
    foreach ($userWithRoles->companyUsers as $companyUser) {
        $roleTypes = $companyUser->roles->pluck('Type')->toArray();
        $userCompanyWiseRoles[] = [
            'CompanyId' => $companyUser->CompanyId,
            'RoleTypes' => $roleTypes
        ];
    }
    return $userCompanyWiseRoles;
});
$selectedCompany = collect($userCompanyWiseRoles)->firstWhere('CompanyId', $request->input('SelectedCompanyId'));
if (!$selectedCompany || !in_array('Developer', $selectedCompany['RoleTypes'])) {
    return ApiResponseTransformer::error([], 'Access Denied.', 403);
}
```

- Requires `SelectedCompanyId` in the request body.
- Loads user → companyUsers → roles, indexed by company.
- 60-second cache, keyed by `UserCompanyWiseRoles-{UserId}`.
- 403 if the user does not carry `Type='Developer'` in the selected company.

### 7.2 `app/Http/Middleware/UserIsAdminOrDeveloper.php` (alias: `admin-or-developer`)

Structurally identical block; differs only in the final guard:

```php
if (!$selectedCompany || empty(array_intersect($selectedCompany['RoleTypes'], ['Developer', 'Administrator']))) {
    return ApiResponseTransformer::error([], 'Access Denied.', 403);
}
```

The cache key is shared with `UserIsDeveloper`, so a single 60s lookup covers both middleware paths for one user.

### 7.3 Aliases (`bootstrap/app.php`)

```php
$middleware->alias([
    'company'            => \App\Http\Middleware\SetCompanyDatabaseConnection::class,
    'developer'          => \App\Http\Middleware\UserIsDeveloper::class,
    'admin-or-developer' => \App\Http\Middleware\UserIsAdminOrDeveloper::class,
]);
```

### 7.4 Scattered role-Type checks

The literals `"Developer"` and `"Administrator"` are also consulted directly in:

- `app/Providers/HorizonServiceProvider.php` (`viewHorizon` gate) — Horizon dashboard restricted to Developer.
- `app/Services/Role/RoleService.php` (`getAssignableRolesByCompany`) — strips Developer from the assignable list unless `WithDeveloper=true`.
- `app/Services/User/UserService.php` (`getCompanyUsers`) — eager-load constraint that hides the Developer role from the company user list.

These are the places that need to keep working unchanged when a Privilege layer is added.

---

## 8. Auth flow

### 8.1 Login

`POST /api/auth/login` → `LoginController::login`
- Validates credentials and SHA1-hashes the password with the user's salt.
- Loads user with `companyUsers.roles` eager-loaded for the active-status / company-presence checks.
- Returns `{ access_token, token_type, expires_in }`.

### 8.2 JWT contents

`User::getJWTCustomClaims()` returns `[]` — no role/permission claims ride in the token. The token is opaque from the SPA's point of view (just a bearer).

### 8.3 Post-login user details

`POST /api/auth/user` → `UserController::authUserDetails` → `Auth::user()`
- Returns the User row with sensitive fields hidden.
- **Does NOT include roles.**

### 8.4 Where roles actually arrive at the SPA

`POST /api/companies/auth-user-companies` → `CompanyService::getAuthUserCompanies`
- Returns the list of companies the user belongs to.
- Each company object carries a `roles` field — an **array of role *Type* strings** (e.g. `['Developer']`) — built server-side by plucking `Type` off the user's `CompanyUserRole` rows for that company.
- This is how the SPA learns the current user's roles.

---

## 9. Frontend integration

### 9.1 `resources/js/stores/authStore.js`

```js
state: () => ({
    user: localStorage.getItem('user') ? JSON.parse(localStorage.getItem('user')) : {},
    roles: []
}),
actions: {
    setRoles(payload) { this.roles = payload; },
    async getAuthUserDetails() {
        let {data} = await User.getAuthUserDetails();
        this.user = data;
        localStorage.setItem('user', JSON.stringify(this.user));
    }
},
getters: {
    getRoles() { return this.roles; }
}
```

`roles` is the array of role *Type* strings for the currently selected company.

### 9.2 `resources/js/stores/companyStore.js`

When a company is selected (initial load or via the company switcher), the store hands the company's `roles` array to `authStore`:

```js
async setSelectedCompanyById(companyId) {
    this.selectedCompany = tempCompany;
    authStore.setRoles(this.selectedCompany.roles);  // ← role propagation
    let {data} = await Module.getActivatedModulesByCompany(tempCompany.Id);
    this.selectedCompanyModules = data;
}
```

### 9.3 `resources/js/composables/useCheckAccess.js`

```js
function hasRoleAccess(roles) {
    const authStore = useAuthStore();
    const authRoles = authStore.getRoles;
    return roles.some(role => authRoles.includes(role));
}

async function checkAccess(roles, module = null) {
    if (!_.isEmpty(roles) && !hasRoleAccess(roles)) {
        await router.push({ name: 'home' });
        notificationStore.showNotification("Access Denied.", "error");
    }
    if (module && !isModuleEnabled(module)) {
        await router.push({ name: 'home' });
        notificationStore.showNotification(`${module} Module Not Enabled.`, "error");
    }
}
```

Array intersection on Type strings. Module presence is checked separately via `useCompanyInfos().isModuleEnabled()`.

### 9.4 Router guard (`resources/js/router/index.js`)

```js
router.beforeEach(async (to, from, next) => {
    const {roles, requiresAuth, module} = to.meta;
    if (requiresAuth) {
        if (isAuthenticated) {
            if (_.isEmpty(companyStore.companies)) await companyStore.fill();
            await checkAccess(roles, module);
            next();
        } else {
            next({ name: 'login', query: {'redirect_to': to.path} });
        }
    }
    // …
});
```

### 9.5 Route meta convention

```js
{
    path: "role/roles",
    name: "roles",
    component: Roles,
    meta: {
        requiresAuth: true,
        requiresCompany: true,
        roles: ['Developer']
    }
}
```

`meta.roles` is the array of role Types allowed; `meta.module` is an optional module-enabled requirement.

### 9.6 Menu visibility (`resources/js/data/menu.js`, `BaseNavigation.vue`)

Each menu node carries:
- `roles: string[]` — visible only if user has one of these role Types
- `moduleSpecific: boolean` and `moduleName: string` — visible only if that module is enabled for the company

`BaseNavigation.vue` filters with `v-if="hasRoleAccess(node.roles)"` and the same `isModuleEnabled` predicate.

### 9.7 Role assignment UI

`resources/js/views/user/EditCompanyUser.vue` fetches the company's assignable roles via `Role.getRolesByCompany(CompanyId)` (which calls `getAssignableRolesByCompany` server-side with `WithDeveloper=false`), shows them as multi-select, and submits `RoleIds[]` to `POST /api/users/company-user/update`. The backend handles the `CompanyUserRole` pivot sync.

---

## 10. Hard-coded role conventions

The literal strings `"Developer"` and `"Administrator"` are the only role Types that carry implicit capabilities today. Everywhere they appear:

| Where | What it gates |
|---|---|
| `app/Http/Middleware/UserIsDeveloper.php` | All routes under the `developer` alias (~20 routes: company CRUD, table/module/application CRUD, translation/language CRUD). |
| `app/Http/Middleware/UserIsAdminOrDeveloper.php` | Routes under the `admin-or-developer` alias. |
| `app/Providers/HorizonServiceProvider.php::viewHorizon` gate | Horizon dashboard access. |
| `app/Services/Role/RoleService.php::getAssignableRolesByCompany` | Hides Developer from non-developer role assignment UI. |
| `app/Services/User/UserService.php::getCompanyUsers` | Hides Developer role from the company users list. |
| `resources/js/data/menu.js` | Menu node `roles: [...]` keys throughout — Developer-only sections (Database, Modules, etc.) and shared sections (`["Developer", "Administrator", "Employee"]`). |
| `resources/js/router/index.js` route metas | Each route's `meta.roles`. |

Any future "elevate this role to do X" workflow has to either (a) add the literal to the appropriate hard-coded check, or (b) wait for the Privilege layer.

---

## 11. Known limitations

- **No granular permissions.** A custom role with `Type='Supervisor'` has zero capabilities — no code path checks for that Type. The only way to grant capabilities today is to use `Type='Developer'` or `Type='Administrator'`.
- **No per-action gating.** "Can create orders" and "can delete orders" cannot be distinguished. It's role-Type or nothing.
- **No module-level role scoping.** A user is either a Developer in a company or they aren't — they can't be a Developer-of-Orders-only.
- **No audit trail.** Role assignments and the `Type` field on roles change without history.
- **`Type` is immutable post-creation.** `RoleService::update()` only writes `Name` and `Description`. Changing the Type of an existing role requires direct DB manipulation.
- **No central privilege catalog.** There is no enumeration anywhere in code of "what can the system do" — the protected verbs are implicit in the route-middleware mapping.
- **Role CRUD endpoints are unprotected by role middleware.** Any authenticated user can create, update, or delete a role for any company they pass a `CompanyId` for. This is a gap that the planned Privilege layer can close (with a `Role.Manage` privilege gating these endpoints).
- **Two near-identical middleware classes** (`UserIsDeveloper` / `UserIsAdminOrDeveloper`) duplicate ~40 lines of identical role-loading code. A privilege-aware base class can collapse them.

---

## See also

- `app/Models/Office/{Role,User,CompanyUser,CompanyUserRole}.php` — the four models.
- `app/Services/Role/RoleService.php` and `app/Repositories/Eloquent/Office/Role/RoleRepository.php` — service/repo layer.
- `app/Http/Controllers/RoleController.php` and `app/Http/Requests/Role/*.php` — controller + validators.
- `app/Http/Middleware/{UserIsDeveloper,UserIsAdminOrDeveloper}.php` — backend enforcement.
- `resources/js/composables/useCheckAccess.js` — frontend enforcement.
- `resources/js/stores/{authStore,companyStore}.js` — frontend role state.
- `resources/js/router/index.js` and `resources/js/data/menu.js` — declarative role gates.
- `routes/api.php` — endpoint list with middleware bindings.
- `CLAUDE.md` — overall architecture; explains why central vs tenant DB matters.
