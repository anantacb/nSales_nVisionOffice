# Installing PHP 8.4 on Ubuntu 20.04 — OS-Specific Guide

Focused guide for getting **PHP 8.4** onto `nvisionphp-prod` (Ubuntu 20.04 Focal,
currently PHP 8.2). Covers the OS-specific gotchas only — the deploy/Node/swap steps live
in `SERVER_UPGRADE_STEPS.md`.

> **Ubuntu 20.04 is supported.** Focal does not ship PHP 8.4 in its own repos (it caps at
> 7.4), but the **ondrej/php PPA** builds 8.4 for focal. So 8.4 installs fine — the only
> work is adding that PPA's repo + signing key correctly.

---

## OS-level prerequisites

### 1. Tooling for adding a PPA (often missing on minimal/cloud 20.04 images)

```bash
sudo apt update
sudo apt install -y software-properties-common ca-certificates lsb-release \
  apt-transport-https gnupg curl
```

### 2. End-of-life note (important, not blocking)

Ubuntu 20.04 reached end of standard support in **April 2025** (ESM/security patches run
to 2030 only with an Ubuntu Pro token). PHP 8.4 via the PPA is unaffected, but plan a
distro upgrade to **22.04 or 24.04** afterward so the OS itself keeps getting security
updates. Do the PHP upgrade first; treat the OS upgrade as a separate follow-up.

### 3. Confirm architecture/DNS are sane

```bash
dpkg --print-architecture     # amd64 expected — PPA serves amd64/arm64
cat /etc/resolv.conf          # must list a working nameserver (PPA fetch needs DNS)
```

---

## Add the ondrej/php repository

You can use `add-apt-repository`, **but on this server it hung** trying to reach the
keyserver. Two reliable paths:

### Path A — the normal way (if keyserver is reachable)

```bash
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
```

### Path B — manual (use this if Path A hangs, as it did here)

The repo line itself works (you already see `Hit:5 ...ondrej/php...`); only the GPG key
import fails, producing `NO_PUBKEY 71DAEAAB4AD4CAB6 / 4F4EA0AAE5267A6C`. Import both keys
over HTTPS:

```bash
for KEY in 71DAEAAB4AD4CAB6 4F4EA0AAE5267A6C; do
  curl -fsSL "https://keyserver.ubuntu.com/pks/lookup?op=get&search=0x${KEY}" \
    | sudo gpg --dearmor -o /etc/apt/trusted.gpg.d/ondrej-${KEY}.gpg
done
sudo apt update
```

If the repo isn't listed at all, add it manually for focal:

```bash
echo "deb https://ppa.launchpadcontent.net/ondrej/php/ubuntu focal main" \
  | sudo tee /etc/apt/sources.list.d/ondrej-php.list
sudo apt update
```

**Verify the repo + key are good before installing:**

```bash
sudo apt update                 # must finish with NO NO_PUBKEY / GPG error
apt-cache policy php8.4-fpm      # must show a Candidate version
```

> The two key IDs (`71DAEAAB4AD4CAB6`, `4F4EA0AAE5267A6C`) are ondrej's PPA signing keys.
> `4F4EA0AAE5267A6C` signs the PHP packages; `71DAEAAB4AD4CAB6` is the secondary PPA key —
> import **both** or `apt update` keeps failing.

---

## Install PHP 8.4 + the extensions this app needs

```bash
sudo apt install -y php8.4-fpm php8.4-cli php8.4-mysql php8.4-mbstring \
  php8.4-xml php8.4-curl php8.4-bcmath php8.4-intl php8.4-zip php8.4-gd \
  php8.4-gmp php8.4-opcache php8.4-readline php8.4-redis php8.4-igbinary
```

| Extension | Why |
|---|---|
| `bcmath` `intl` `gd` `gmp` | **New on this server** (absent from 8.2). `intl` = `akaunting/laravel-money`; `gmp` = `google/cloud-translate` deps; `gd` = image handling; `bcmath` = Laravel core. |
| `mysql` (pdo_mysql) | Talks to the remote MySQL / multi-tenant DBs. |
| `mbstring` `xml` `curl` `zip` `opcache` | Laravel 13 baseline. |
| `redis` `igbinary` | Preserve your existing phpredis setup so `REDIS_CLIENT=phpredis` keeps working. |

`openssl`, `sodium`, `ftp`, `ctype`, `fileinfo`, `tokenizer` are bundled with
`php8.4-cli`/`-common` — no separate package.

---

## Make 8.4 the default and wire up FPM

### Switch the CLI default from 8.2 to 8.4

```bash
sudo update-alternatives --set php /usr/bin/php8.4
php -v                                              # expect PHP 8.4.x
php -m | grep -E 'bcmath|intl|gd|gmp|redis|pdo_mysql'   # all present
```

### Carry over your php.ini tuning

Focal will have installed php8.4 with default ini values — copy your tuned settings from
the 8.2 files so limits don't regress:

```bash
# compare, then port these keys: memory_limit, upload_max_filesize, post_max_size,
# max_execution_time, date.timezone, opcache.*  (cli AND fpm)
sudo diff /etc/php/8.2/fpm/php.ini /etc/php/8.4/fpm/php.ini
sudo diff /etc/php/8.2/cli/php.ini /etc/php/8.4/cli/php.ini
```

Edit `/etc/php/8.4/fpm/php.ini` and `/etc/php/8.4/cli/php.ini` accordingly. Also check the
FPM **pool** config if you customised it (`pm.max_children`, user/group, listen socket):

```bash
sudo diff /etc/php/8.2/fpm/pool.d/www.conf /etc/php/8.4/fpm/pool.d/www.conf
```

### Start the 8.4 FPM service

```bash
sudo systemctl enable --now php8.4-fpm
sudo systemctl status php8.4-fpm        # active (running)
ls /run/php/php8.4-fpm.sock             # socket exists for nginx
```

> nginx still points at the 8.2 socket until you change `fastcgi_pass` to
> `/run/php/php8.4-fpm.sock` — see `SERVER_UPGRADE_STEPS.md` step 2.

---

## Verify the install

```bash
php -v                                   # 8.4.x (CLI)
php8.4 -m | sort                         # full extension list
php-fpm8.4 -v                            # 8.4.x (FPM)
sudo php-fpm8.4 -t                       # FPM config test: "successful"
```

## After the app is confirmed working — remove 8.2

Do this only once nginx + Horizon are running on 8.4 and the app is verified, so you can
roll back to 8.2 if needed in the meantime.

```bash
sudo apt purge 'php8.2*'
sudo apt autoremove -y
```
