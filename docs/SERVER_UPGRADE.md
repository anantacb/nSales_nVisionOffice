# Remote Server Upgrade Guide — Laravel 13 / PHP 8.4 / Node 20+ Migration

## Context

`nVisionOffice` was migrated to **Laravel 13 (PHP 8.4)** + **Vue 3 / Vite 6** with all
packages bumped (`feature/laravel-migration` branch). The production server must be
brought up to the new runtime baseline *before* this branch is deployed, or the app will
fail to boot (`composer install` aborts on the `php ^8.4` platform requirement) and the
frontend won't build (Vite 6 refuses Node < 18.18).

**Environment:** self-managed Ubuntu VPS over SSH (`nvisionphp-prod`), the production asset
build (`npm run build`) runs **on the server**, and **Redis is already installed and
running**. The MySQL database is **remote** (not on this box) — no MySQL install needed here.

This app manages tenant schemas dynamically — it does *not* rely on Laravel migrations —
so the upgrade is runtime-only with no schema risk.

---

## Gap analysis — actual state of `nvisionphp-prod` (2026-06-25)

Captured from the live server. **Only 4 things need changing:**

| Component | Current | Required | Action |
|---|---|---|---|
| OS | Ubuntu 20.04 (Focal) | — | Keep; php8.4 installs via ondrej PPA. ⚠️ 20.04 is past EOL — plan an OS bump to 22.04/24.04 later (not blocking). |
| **PHP** | **8.2.4** | 8.4 | **Upgrade** (Step 1) |
| **PHP ext** `bcmath` `intl` `gd` `gmp` | **❌ missing** | required | **Install** with 8.4 — these 4 are absent from `php -m`. |
| PHP ext `redis` `igbinary` `pdo_mysql` `mbstring` `xml` `curl` `zip` `sodium` `ftp` `opcache` | ✓ present (8.2) | required | Re-install the 8.4 builds. |
| **Node.js** | **18.15.0** | 20/22 LTS | **Upgrade** (Step 4) — 18.15 is below Vite 6's safe floor. |
| **Swap** | **0 B** (7.7 GB RAM, ~3.2 GB free) | ~8 GB headroom for build | **Add swap (mandatory)** — `npm run build` allocates 8 GB and will OOM-kill otherwise. |
| Composer | 2.5.5 | 2.x | ✅ No change. |
| nginx | 1.18.0 | — | Repoint fpm socket to 8.4 (Step 2). |
| Redis | 5.0.7 running | running | ✅ No change. `redis` + `igbinary` PHP exts present → can keep `REDIS_CLIENT=phpredis`. |
| Supervisor | 4.1.0 | — | ✅ Restart Horizon under php8.4 (Step 6). |
| MySQL | not installed (remote DB) | — | ✅ N/A — correct by design. |

---

## Target requirements (derived from the repo)

| Component | Required | Source |
|---|---|---|
| PHP | **8.4.x** (CLI + FPM) | `composer.json` `php ^8.4`, `composer.lock` platform |
| Composer | 2.x | Laravel 13 toolchain |
| Node.js | **20 LTS or 22 LTS** (min 18.18) | `vite ^6` in `package.json` |
| Build RAM | ~8 GB available during build | `npm run build` uses `--max-old-space-size=8192` |
| Redis | running (already done) | `predis ^2.2` + `laravel/horizon`, `REDIS_CLIENT=predis` |
| MySQL | 8.0+ (multi-tenant, existing) | `config/database.php` |
| Web server | nginx + php8.4-fpm socket | manual VPS |
| Supervisor | running, pointed at php8.4 (Horizon) | `laravel/horizon` |

**Required PHP extensions** (Laravel 13 core + this app's packages):
`fpm, cli, mysql (pdo_mysql), mbstring, xml, curl, bcmath, intl, zip, gd, gmp, opcache`
plus bundled `openssl, sodium, ftp, ctype, fileinfo, tokenizer`.

- `intl` — required by `akaunting/laravel-money` (`NumberFormatter`).
- `ftp` — required by `league/flysystem-ftp` (Admin FTP feature); bundled with php-common.
- `gmp` — used by `google/cloud-translate` deps.
- `phpredis` (`php8.4-redis`) is **optional** — `REDIS_CLIENT=predis` is pure-PHP and works
  without it. Install it only as a perf upgrade (then optionally flip `REDIS_CLIENT=phpredis`).

---

## Pre-flight: capture the current state (run first)

```bash
cat /etc/os-release | head -2 ; free -h ; nproc
php -v ; php -m ; which php8.4 || echo "php8.4 MISSING"
node -v ; npm -v ; composer --version
nginx -v 2>&1 ; mysql --version
redis-server --version ; supervisorctl status 2>&1
```

This confirms the exact gap. The steps below are safe regardless of starting versions;
skip any step already satisfied.

> **OS check:** native `php8.4` packages need Ubuntu 24.04+ or Debian 12+. On older
> releases you add a third-party repo (steps below) rather than upgrading the OS.

---

## Step 1 — Install PHP 8.4 + extensions

**Ubuntu** (adds ondrej PPA, which carries 8.4 for all supported releases):

```bash
sudo add-apt-repository ppa:ondrej/php -y && sudo apt update
sudo apt install -y php8.4-fpm php8.4-cli php8.4-mysql php8.4-mbstring \
  php8.4-xml php8.4-curl php8.4-bcmath php8.4-intl php8.4-zip php8.4-gd \
  php8.4-gmp php8.4-opcache php8.4-readline php8.4-redis php8.4-igbinary
```

> `bcmath`, `intl`, `gd`, `gmp` are **new** on this server (missing from 8.2). The rest
> mirror your existing 8.2 extension set. `php8.4-redis` + `php8.4-igbinary` preserve your
> current phpredis setup so you can keep `REDIS_CLIENT=phpredis`.

**Debian** uses the Sury repo instead of the PPA:

```bash
sudo apt install -y apt-transport-https lsb-release ca-certificates curl
curl -sSL https://packages.sury.org/php/README.txt | sudo bash -x
sudo apt update   # then the same php8.4-* install line as above
```

Make 8.4 the CLI default and verify:

```bash
sudo update-alternatives --set php /usr/bin/php8.4
php -v   # expect 8.4.x
```

Copy any custom php.ini tuning (memory_limit, upload_max_filesize, post_max_size,
opcache.*, date.timezone) from the old `php*-fpm`/`cli` ini into the php8.4 equivalents
(`/etc/php/8.4/fpm/php.ini`, `/etc/php/8.4/cli/php.ini`). Then:

```bash
sudo systemctl enable --now php8.4-fpm
```

## Step 2 — Point nginx at the php8.4-fpm socket

Edit the site's nginx server block (`/etc/nginx/sites-available/<site>`): change the
`fastcgi_pass` to the new socket, then reload.

```bash
# fastcgi_pass unix:/run/php/php8.4-fpm.sock;
sudo nginx -t && sudo systemctl reload nginx
```

After cutover, remove the old PHP version to avoid a stray FPM pool (only once 8.4 is
confirmed live): `sudo apt purge 'php8.[0-3]*' && sudo apt autoremove -y`.

## Step 3 — Composer (already satisfied)

This server already runs **Composer 2.5.5** — no change needed. (Optionally
`sudo composer self-update --2` to pick up the latest 2.x patch.)

## Step 4 — Install Node 20/22 + guard build memory

```bash
curl -fsSL https://deb.nodesource.com/setup_22.x | sudo -E bash -
sudo apt install -y nodejs
node -v ; npm -v   # expect v22.x (or v20.x)
```

The build calls `node --max-old-space-size=8192`. This server has 7.7 GB RAM and **0 B
swap**, with only ~3.2 GB free — `npm run build` *will* be OOM-killed. **Adding swap is
mandatory here** (do this before the first build):

```bash
sudo fallocate -l 4G /swapfile && sudo chmod 600 /swapfile
sudo mkswap /swapfile && sudo swapon /swapfile
echo '/swapfile none swap sw 0 0' | sudo tee -a /etc/fstab
free -h   # confirm Swap: 4.0Gi
```

## Step 5 — Redis (already running) — verify only

```bash
redis-cli ping            # expect PONG
```

Confirm production `.env` uses Redis-backed drivers (the commented hints in `.env.example`):
`CACHE_DRIVER=redis`, `QUEUE_CONNECTION=redis`, `SESSION_DRIVER` as desired,
`REDIS_CLIENT=predis` (or `phpredis` only if Step 1's optional ext was installed).
No Redis install needed.

## Step 6 — Repoint Supervisor / Horizon at php8.4

Horizon workers must run under the new PHP binary. Update the supervisor program command
(`/etc/supervisor/conf.d/horizon.conf`) to `php8.4 /path/artisan horizon` (or just `php`
now that the default is 8.4), then:

```bash
sudo supervisorctl reread && sudo supervisorctl update
sudo supervisorctl restart all
```

## Step 7 — Deploy the migrated branch

```bash
cd /path/to/app
php artisan down
git fetch && git checkout feature/laravel-migration && git pull
composer install --no-dev --optimize-autoloader     # now succeeds on PHP 8.4
npm ci && npm run build                              # Vite 6 build into public/build/
php artisan migrate --force                          # baseline Laravel tables only
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan horizon:terminate                        # Horizon restarts via supervisor
php artisan up
```

> Per the multi-tenant design, there is **no app schema migration** — tenant tables are
> managed at runtime via `TableController`. `migrate --force` only touches the 4 baseline
> Laravel tables and is safe/idempotent.

---

## Verification (end-to-end)

1. `php -v` → 8.4.x; `php -m` includes `mysql mbstring intl gd zip bcmath gmp curl`.
2. `node -v` → 20.x/22.x; `npm run build` completes without OOM; `public/build/manifest.json` regenerated.
3. `composer install --no-dev` exits 0 (no platform-requirement abort).
4. App boots: hit the SPA host, log in (JWT) → confirms `APP_KEY`/`JWT_SECRET` intact.
5. Tenant request works: any endpoint with a `CompanyId` returns data (proves
   `SetCompanyDatabaseConnection` + pdo_mysql across the multi-DB split).
6. `php artisan horizon:status` → running; dispatch a job, confirm it processes.
7. `redis-cli ping` → PONG; `sudo supervisorctl status` → horizon RUNNING.
8. Tail `storage/logs` and php8.4-fpm log for class-not-found / missing-extension errors.

## Rollback

Old PHP versions remain installed until Step 2's purge — to roll back before that, point
`fastcgi_pass` back to the previous socket, `git checkout` the prior tag, `composer install`,
reload nginx + restart supervisor. Keep a DB backup before Step 7 regardless.
