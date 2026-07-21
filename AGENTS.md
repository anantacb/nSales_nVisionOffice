# AGENTS.md

## Stack
Laravel 12 + PHP 8.2 + Vue 3 SPA + MySQL + Redis (predis). Built with Vite (`npm run dev` / `npm run build`).

## Setup
- `composer install && npm install`
- `php artisan key:generate && php artisan jwt:secret && php artisan log-viewer:publish`
- Dev: `php artisan serve` + `npm run dev` (separate terminals)
- Valet: `valet isolate php@8.2` (if default PHP ≠ 8.2)
- Clear caches: `php artisan cache:clear && php artisan config:clear && php artisan route:clear`

## Multi-Tenancy (dual-DB)
- **`mysql`** (default): central office data — `App\Models\Office\*` (Companies, Users, Modules)
- **`mysql_company`**: per-company DBs — `App\Models\Company\*` with `protected $connection = 'mysql_company'` (Items, Orders, Customers)
- **`mysql_dev`**: dev mirror for `db:copy-prod-to-dev` command
- **`mysql_admin`**: NSALES_ADMIN database
- `CLOUD_SQL_MIGRATED=0` controls `DbHelpers::connectDB()` vs `connectCloudSqlDB()`
- `DB_TEMPLATE_DATABASE=NVISION_TEMPLATE` for company cloning
- Connection switched per-request via `company` middleware (`SetCompanyDatabaseConnection`), which requires `CompanyId` in request body and calls `CompanyService::setCompanyDatabaseConnection()`. Company data cached 24h under key `company_{id}`.

## Models
- `BaseModel`: `$primaryKey = 'Id'`, `CREATED_AT = 'InsertTime'`, `UPDATED_AT = 'UpdateTime'`, `DELETED_AT = 'DeleteTime'`, `$guarded = []`
- Some models override `$primaryKey` (e.g., `Orderhead` uses `UUID`, `keyType = 'string'`, `incrementing = false`)
- Office models: no explicit connection (defaults to `mysql`)
- Company models: `protected $connection = 'mysql_company'`
- All Eloquent relationships pass explicit foreign/local keys

## API
- All endpoints POST-only, JSON-in/JSON-out, under `prefix('api')`
- Auth: JWT via `php-open-source-saver/jwt-auth`. Custom password hashing: `strtoupper(sha1($salt . $password))`
- Response via `ApiResponseTransformer::success()` / `error()` — shape: `{ success, message, data, errors, pagination }`

## Middleware (route order)
- `auth:api` — JWT auth
- `developer` — `UserIsDeveloper` (developer-only routes)
- `admin-or-developer` — `UserIsAdminOrDeveloper`
- `company` — `SetCompanyDatabaseConnection` (requires `CompanyId` in body)

## Service / Repository Pattern
- Interface + Implementation co-located: `app/Services/{Domain}/{Name}ServiceInterface.php` / `{Name}Service.php`
- Repositories at `app/Repositories/Eloquent/{Office|Company|Admin}/{Name}/`, extend `BaseRepository`
- Bound in `ServiceServiceProvider` / `RepositoryServiceProvider` — constructor-inject interfaces
- `ServiceFactory` does **not** exist — services resolved via Laravel container
- Codegen: `php artisan make:service {ServiceName}` and `php artisan make:repository --model= --type=office|company`

## Custom Artisan Commands
- `db:copy-prod-to-dev --dbType= --dbName=` — copy MySQL DB prod→dev
- `make:service {service}` — scaffold service + interface
- `make:repository --model= --type=office` — scaffold repository + interface
- Postmark/email/translation sync commands in `app/Console/Commands/`

## Queue
Laravel Horizon. Start: `php artisan horizon`

## Frontend (Vue 3)
- SPA mounted in `resources/views/app.blade.php` via `@vite`; catch-all web route renders it
- Pinia stores: `authStore`, `companyStore`, `notificationStore`, `templateStore`
- Two layouts: `Backend` (auth pages) and `Simple` (login)
- Route guards: `requiresAuth`, `requiresCompany`, role-based + module permission checks
- Dependencies: Bootstrap 5, Sass, OneUI theme, CKEditor, FullCalendar, Chart.js, CodeMirror, Tiptap, SweetAlert2, custom `DataGrid` component

## Testing
- PHPUnit with `Unit` and `Feature` suites. Minimal coverage (ExampleTest only).
- Run: `php artisan test`
- JWT auth in tests requires JWT secret. In-memory SQLite not configured (commented out in phpunit.xml).

## Third-Party Integrations
- Postmark (transactional email + template management)
- BunnyCDN (CDN)
- B2B GraphQL API (`B2B_GQL_API_URL`)
- NVM GraphQL API (`NVM_GQL_API_URL`)
- nSales Admin Django API (`DJANGO_API_URL`)
- Google Cloud Translate
- FTP sync (`SYNC_FTP_*`)

## Conventions
- Controllers must not contain business logic (delegate to Services).
- Logging uses `Log::info`/`Log::error` in Services, not Controllers.
- Use `ApiResponseTransformer` for all JSON responses.
- Company-scoped routes require `CompanyId` in POST body, not URL segments.
- Don't use Laravel default `created_at`/`updated_at` — use `InsertTime`/`UpdateTime`.
- In non-local envs, force HTTPS via `AppServiceProvider` (`URL::forceScheme('https')` for `production`/`development`).
