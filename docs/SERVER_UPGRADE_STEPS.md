# What To Do — `nvisionphp-prod` Upgrade Checklist

A copy-paste, in-order runbook for upgrading the production server to the Laravel 13 /
PHP 8.4 / Node 22 baseline. Run as a user with sudo. See `SERVER_UPGRADE.md` for the
full gap analysis and reasoning.

**Server facts:** Ubuntu 20.04, PHP 8.2 → needs 8.4, Node 18.15 → needs 22, 0 B swap →
needs swap, Redis/Composer/nginx/Supervisor already OK, MySQL is remote (not on this box).

---

## 0. Fix the ondrej PPA GPG key (do this first)

The `ondrej/php` repo is already added and reachable, but `apt update` fails with
`NO_PUBKEY 71DAEAAB4AD4CAB6 / 4F4EA0AAE5267A6C` because the signing keys aren't imported
(`add-apt-repository` hung on the keyserver earlier). Import them over HTTPS:

```bash
for KEY in 71DAEAAB4AD4CAB6 4F4EA0AAE5267A6C; do
  curl -fsSL "https://keyserver.ubuntu.com/pks/lookup?op=get&search=0x${KEY}" \
    | sudo gpg --dearmor -o /etc/apt/trusted.gpg.d/ondrej-${KEY}.gpg
done
sudo apt update
apt-cache policy php8.4-fpm     # must show a Candidate version, no NO_PUBKEY error
```

Fallback if `keyserver.ubuntu.com` hangs (the box can still reach Launchpad — that's how
the repo shows `Hit:5`):

```bash
sudo apt-key adv --keyserver hkps://keyserver.ubuntu.com --recv-keys 71DAEAAB4AD4CAB6 4F4EA0AAE5267A6C
sudo apt update
```

**Gate:** do not continue until `apt update` is clean and `apt-cache policy php8.4-fpm`
shows a candidate. ✅

---

## 1. Install PHP 8.4 + extensions

```bash
sudo apt install -y php8.4-fpm php8.4-cli php8.4-mysql php8.4-mbstring \
  php8.4-xml php8.4-curl php8.4-bcmath php8.4-intl php8.4-zip php8.4-gd \
  php8.4-gmp php8.4-opcache php8.4-readline php8.4-redis php8.4-igbinary
```

`bcmath`, `intl`, `gd`, `gmp` are new (missing from your 8.2). The rest mirror the 8.2 set;
`redis` + `igbinary` keep your phpredis setup.

Make 8.4 the CLI default and verify:

```bash
sudo update-alternatives --set php /usr/bin/php8.4
php -v                          # expect 8.4.x
php -m | grep -E 'bcmath|intl|gd|gmp|redis|pdo_mysql'   # all present
```

Copy your tuning from the 8.2 ini into the 8.4 ini (`memory_limit`, `upload_max_filesize`,
`post_max_size`, `opcache.*`, `date.timezone`):
`/etc/php/8.4/fpm/php.ini` and `/etc/php/8.4/cli/php.ini`. Then:

```bash
sudo systemctl enable --now php8.4-fpm
```

## 2. Point nginx at the php8.4-fpm socket

Edit the site config (`/etc/nginx/sites-available/<site>`), change the fastcgi socket:

```nginx
fastcgi_pass unix:/run/php/php8.4-fpm.sock;
```

```bash
sudo nginx -t && sudo systemctl reload nginx
```

## 3. Add swap (mandatory — currently 0 B)

`npm run build` allocates 8 GB; with 7.7 GB RAM and no swap it will be OOM-killed.

```bash
sudo fallocate -l 4G /swapfile && sudo chmod 600 /swapfile
sudo mkswap /swapfile && sudo swapon /swapfile
echo '/swapfile none swap sw 0 0' | sudo tee -a /etc/fstab
free -h                         # confirm Swap: 4.0Gi
```

## 4. Upgrade Node 18 → 22 LTS

```bash
curl -fsSL https://deb.nodesource.com/setup_22.x | sudo -E bash -
sudo apt install -y nodejs
node -v ; npm -v                # expect v22.x
```

## 5. Already OK — no action

- **Composer** 2.5.5 ✅
- **Redis** 5.0.7 running ✅
- **Supervisor** 4.1.0 ✅ (Horizon restarted in step 7)
- **MySQL** — remote DB, nothing to install here ✅

## 6. Deploy the migrated branch

```bash
cd /path/to/app
php artisan down
git fetch && git checkout feature/laravel-migration && git pull
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force      # baseline Laravel tables only (no app-schema migrations)
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan up
```

## 7. Restart Horizon under php8.4

```bash
sudo supervisorctl restart all
php artisan horizon:terminate    # supervisor respawns it under the new PHP
sudo supervisorctl status        # horizon RUNNING
```

---

## Verify

```bash
php -v                                   # 8.4.x
php -m | grep -E 'bcmath|intl|gd|gmp'    # all four present
node -v                                  # 22.x
free -h                                  # Swap 4.0Gi
ls public/build/manifest.json            # assets built
redis-cli ping                           # PONG
php artisan horizon:status               # running
```

Then in the app: log in (JWT works → APP_KEY/JWT_SECRET intact) and hit any endpoint with
a `CompanyId` (tenant DB switch + remote MySQL work). Tail `storage/logs` and the
php8.4-fpm log for any missing-extension / class-not-found errors.

## Rollback

The old PHP 8.2 stays installed until you purge it. To roll back: point nginx
`fastcgi_pass` back to the 8.2 socket, `git checkout` the previous tag,
`composer install`, `sudo systemctl reload nginx`, `sudo supervisorctl restart all`.
Take a DB backup before step 6 regardless.

## Cleanup (only after everything is verified stable)

```bash
sudo apt purge 'php8.2*' && sudo apt autoremove -y
```