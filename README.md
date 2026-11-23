<p align="center"><img src="public/shoebill.svg" width="96" alt="Shoebill logo"></p>

<h1 align="center">Shoebill</h1>

<p align="center">
  A game server panel that merges <a href="https://github.com/pelican-dev/panel">Pelican</a> and <a href="https://github.com/pterodactyl/panel">Pterodactyl</a> into one interface.<br>
  <a href="README.ja.md">日本語</a> · <a href="NOTICE.md">Credits</a> · <a href="license">AGPL-3.0</a>
</p>

> **Status: 1.0.0-alpha.1.** Usable for testing. Not yet verified against real Pterodactyl Wings in production. Back up before migrating a live panel.

## Contents
- [What it is](#what-it-is)
- [Features](#features)
- [Requirements](#requirements)
- [Installation (Linux server)](#installation-linux-server)
- [Installation (Docker)](#installation-docker)
- [Connecting Wings](#connecting-wings)
- [Configuration reference](#configuration-reference)
- [Admin settings in the panel](#admin-settings-in-the-panel)
- [Migrating from Pterodactyl or Pelican](#migrating-from-pterodactyl-or-pelican)
- [Updating](#updating)
- [Local development](#local-development)
- [Troubleshooting](#troubleshooting)
- [Contact](#contact)
- [Credits and license](#credits-and-license)

## What it is
Shoebill uses Pelican's backend and admin panel (Laravel 13, Filament 5) and brings Pterodactyl's features and visual
cues into the same UI, roughly 6:4 Pelican to Pterodactyl. Every feature either panel has is kept. Nodes can run
**Pelican Wings or Pterodactyl Wings**, and eggs in both formats import and export.

## Features
**From both panels:** servers, nodes, allocations, eggs, users, subusers, roles, databases, backups, schedules,
file manager, SFTP, console, activity log, API (application and client), webhooks, plugins, OAuth, 2FA and passkeys.

**Added by Shoebill:**
| Feature | Where |
|---|---|
| Status page: today / 7-day / 30-day / all-time averages of players, CPU and memory, plus uptime and a chart | Server → Status |
| Game-agnostic player counts (Minecraft Java, Minecraft Bedrock, Steam/Source A2S). Other games show CPU and memory only | Admin → Eggs → *Player query* |
| Player list and history (first seen, last seen, play time) | Server → Status |
| Crash alerts (panel notification + email) when a server stops without a stop/restart/kill | Profile notifications |
| Uptime rate per period, and fleet uptime on the admin dashboard | Status page, Admin dashboard |
| Favourite servers (star a server to keep it at the top) | Server list |
| Feature switches: turn each Shoebill feature on/off globally, per node or per server | Admin → Advanced → Features, node/server *Features* tab |
| Pelican **and** Pterodactyl Wings per node, plus a Wings-less test mode | Admin → Nodes → Advanced |
| Egg export in Pterodactyl `PTDL_v2` format (import of `PTDL_v1/v2` and `PLCN` was already there) | Admin → Eggs → Export |
| Announcements banner with schedule and Markdown | Admin → Advanced → Announcements |
| Node location, egg tags and reserved tags (Pterodactyl 2.0) | Admin → Nodes |
| Accent color (panel default and per user), animation level, cursor spotlight | Admin → Settings, Profile |
| Admin-managed languages and a language switcher (English, 日本語, 한국어, 简体中文, 繁體中文 and 25+ more) | Admin → Settings → Languages |
| IP addresses blurred on screen until hovered (safe for streaming) | Everywhere |
| One-command migration from Pterodactyl or Pelican | `php artisan shoebill:migrate-from` |

Planned (approved): mod/plugin installer, server templates, scheduled restarts, player management, Discord integration,
resource alerts, public status page, command palette, console tools, mobile layout, usage reports, bulk actions,
audit log export, backup auto-cleanup.

## Requirements
| | Version |
|---|---|
| OS | Linux (Ubuntu 22.04/24.04, Debian 12 tested by upstream). macOS works for development |
| PHP | **8.4 or 8.5** with `bcmath`, `curl`, `gd`, `intl`, `mbstring`, `mysql`/`pgsql`/`sqlite3`, `xml`, `zip` |
| Database | MySQL 8, MariaDB 10.6+, PostgreSQL 14+ or SQLite |
| Web server | Nginx, Apache or Caddy (PHP-FPM) |
| Tools | Composer 2, Node.js 22+ and Yarn 1 (only to build front-end assets) |
| Optional | Redis (cache, queue, sessions) |
| Game nodes | [Pelican Wings](https://github.com/pelican-dev/wings) or [Pterodactyl Wings](https://github.com/pterodactyl/wings) with Docker |

## Installation (Linux server)
The steps below use Ubuntu 24.04, Nginx, MariaDB and the path `/var/www/shoebill`. Run them as root or with `sudo`.

### 1. Install dependencies
```bash
apt update && apt install -y software-properties-common curl git unzip nginx mariadb-server redis-server
add-apt-repository -y ppa:ondrej/php && apt update
apt install -y php8.4 php8.4-{fpm,cli,bcmath,curl,gd,intl,mbstring,mysql,sqlite3,xml,zip,redis}
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
curl -fsSL https://deb.nodesource.com/setup_22.x | bash - && apt install -y nodejs && npm i -g yarn
```

### 2. Download Shoebill
```bash
git clone https://github.com/yamashi8040-ai/Shoebill.git /var/www/shoebill
cd /var/www/shoebill
```

### 3. Install PHP and front-end packages
```bash
composer install --no-dev --optimize-autoloader
yarn install --frozen-lockfile && yarn build
cp .env.example .env
php artisan key:generate --force
```
> Back up `APP_KEY` from `.env` somewhere safe. It encrypts stored secrets; without it the database cannot be read.

### 4. Create the database
```bash
mysql -u root -e "CREATE DATABASE shoebill; CREATE USER 'shoebill'@'127.0.0.1' IDENTIFIED BY 'CHANGE_ME'; GRANT ALL PRIVILEGES ON shoebill.* TO 'shoebill'@'127.0.0.1'; FLUSH PRIVILEGES;"
```

### 5. Configure the environment
Either open `https://your-domain/installer` in a browser after step 7 (a guided web installer), or use the CLI:
```bash
php artisan p:environment:setup      # URL, timezone, cache/session/queue drivers
php artisan p:environment:database   # database connection
php artisan p:environment:mail       # mail (needed for crash alerts and password resets)
php artisan migrate --seed --force
php artisan p:user:make              # first admin account (answer "yes" to admin)
```
Then set `APP_INSTALLED=true` in `.env` if you used the CLI. Also set `SHOEBILL_SOURCE_URL` (see [license](#credits-and-license)).

### 6. File permissions
```bash
chown -R www-data:www-data /var/www/shoebill
chmod -R 755 storage/* bootstrap/cache/
```

### 7. Web server (Nginx)
Create `/etc/nginx/sites-available/shoebill.conf`:
```nginx
server {
    listen 80;
    server_name panel.example.com;
    root /var/www/shoebill/public;
    index index.php;
    client_max_body_size 100m;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known) { deny all; }
}
```
```bash
ln -s /etc/nginx/sites-available/shoebill.conf /etc/nginx/sites-enabled/
nginx -t && systemctl restart nginx
```
Use HTTPS in production, for example with Certbot: `apt install -y certbot python3-certbot-nginx && certbot --nginx -d panel.example.com`.

### 8. Scheduler (cron)
Shoebill records server stats every minute and runs schedules, so the Laravel scheduler must run:
```bash
(crontab -u www-data -l 2>/dev/null; echo "* * * * * php /var/www/shoebill/artisan schedule:run >> /dev/null 2>&1") | crontab -u www-data -
```

### 9. Queue worker
Crash alerts, emails and webhooks go through the queue. Create the systemd service with:
```bash
php artisan p:environment:queue-service --service-name=shoebill-queue
```
or write `/etc/systemd/system/shoebill-queue.service` yourself:
```ini
[Unit]
Description=Shoebill Queue Worker
After=redis-server.service

[Service]
User=www-data
Group=www-data
Restart=always
ExecStart=/usr/bin/php /var/www/shoebill/artisan queue:work --tries=3
StartLimitInterval=180
StartLimitBurst=30
RestartSec=5s

[Install]
WantedBy=multi-user.target
```
```bash
systemctl enable --now shoebill-queue
```

Sign in at `https://panel.example.com/login` with the admin you created.

## Installation (Docker)
```bash
git clone https://github.com/yamashi8040-ai/Shoebill.git && cd Shoebill
# edit compose.yml: APP_URL, LE_EMAIL and the mail settings
docker compose up -d --build
```
The image includes Caddy (automatic HTTPS), the queue worker and the scheduler. Data lives in the `pelican-data`
volume (the volume name is kept from upstream). Open `https://your-domain/installer` to finish setup.
`compose-full-stack.yml` also starts MariaDB and Redis.

## Connecting Wings
1. **Admin → Nodes → Create**: enter the FQDN (or IP) Wings is reachable on, memory/disk limits and ports.
2. **Advanced → Wings type**: choose **Pelican Wings** or **Pterodactyl Wings**. The config path and data folder
   defaults follow the choice (`/etc/pelican/config.yml` or `/etc/pterodactyl/config.yml`).
3. Install Wings on the node by following its own guide
   ([Pelican](https://pelican.dev/docs/wings/install) / [Pterodactyl](https://pterodactyl.io/wings/1.0/installing.html)).
4. **Configuration tab**: copy the generated `config.yml` (or the auto-deploy command) to the node, then start Wings
   (`systemctl enable --now wings`).
5. **Allocations**: add the IPs and ports servers may use.

**Behind Cloudflare Tunnel or another HTTPS proxy:** set *SSL* to "HTTPS (behind a proxy)". The panel connects on 443
and Wings listens on 8080.

**Test mode (no Wings):** turn on *Test mode* on a node to let the panel answer for Wings. You can create servers,
allocations and see usage without a real node (servers don't actually start). Turn it off once real Wings is connected.

Pterodactyl Wings lacks a few Pelican Wings endpoints; Shoebill covers them on the panel side (file search within the
current folder, crash detection from minute samples). Install logs and diagnostics show as unsupported on those nodes.

## Configuration reference
All settings live in `.env`. Most can also be changed in **Admin → Settings**, which writes `.env` for you.

### Shoebill settings
| Key | Default | Description |
|---|---|---|
| `SHOEBILL_SOURCE_URL` | `https://github.com/yamashi8040-ai/Shoebill` | Link to the source of **your** version, shown in the footer and on `/credits` (AGPL-3.0 §13). Change it if you modify Shoebill |
| `APP_ACCENT` | *(brand purple `#6568D2`)* | Default accent color as `#rrggbb`. Users can pick their own on their profile |
| `APP_LANGUAGES` | *(all)* | Comma-separated language codes users may choose, e.g. `en,ja,ko,zh_CN,zh_TW`. English is always on |
| `APP_LOCALE` | `en` | Default language for new users and guests |
| `APP_MOTION` | `rich` | Animation level: `off`, `standard` or `rich`. Users with "reduce motion" always get `off` |
| `APP_SPOTLIGHT` | `true` | Cursor spotlight on cards |
| `PANEL_SEND_CRASH_NOTIFICATION` | `true` | Email/notify owners when their server crashes |

### Common panel settings (from Pelican)
| Key | Description |
|---|---|
| `APP_URL` | Public URL of the panel, with `https://` |
| `APP_KEY` | Encryption key. Generate once, never change, keep a backup |
| `APP_NAME`, `APP_LOGO`, `APP_FAVICON` | Branding |
| `APP_ENV`, `APP_DEBUG` | `production` and `false` on a live server |
| Timezone | `timezone` in `config/app.php` (default `UTC`); it decides when "today" starts on the Status page |
| `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | Database |
| `CACHE_STORE`, `SESSION_DRIVER`, `QUEUE_CONNECTION` | `redis` recommended (`php artisan p:redis:setup`), otherwise `file`/`database` |
| `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS` | Outgoing mail |
| `APP_2FA_REQUIRED` | `0` off, `1` admins, `2` everyone |
| `TRUSTED_PROXIES` | Set when behind a reverse proxy or Cloudflare |

After editing `.env` by hand, run `php artisan optimize:clear` and restart the queue worker.

## Admin settings in the panel
- **Settings → General**: name, logo, favicon, accent color, animation level, cursor spotlight, units.
- **Settings → Languages**: tick the languages users can choose; **Add language** creates `lang/<code>/` from the
  English files for translators to fill in.
- **Settings → Misc**: crash notification email and other notification switches.
- **Advanced → Features**: turn Status page, player history, crash alerts and favourite servers on or off panel-wide.
  Each node and server has its own **Features** tab with *Inherit / On / Off*. Priority: server → node → global → on.
- **Advanced → Announcements**: banners shown on every page, with start/end time.
- **Eggs → (egg) → Player query**: pick the query protocol for the Status page, or leave empty to auto-detect.

## Migrating from Pterodactyl or Pelican
1. Back up the old database and the old `.env`.
2. Install Shoebill (steps 1–3 above) on the same or a new machine, with access to the old database.
3. Run:
   ```bash
   php artisan shoebill:migrate-from --env=/var/www/pterodactyl/.env
   ```
   It copies `APP_KEY` and `DB_*`, detects whether the database is Pterodactyl or Pelican, and upgrades it in place.
   Users, passwords, servers, nodes and eggs are kept. Coming from Pterodactyl, every node is set to
   *Pterodactyl Wings*, so the nodes keep running unchanged.
4. Point your web server at Shoebill and continue from step 6.

## Updating
Do **not** use `php artisan p:update`: it downloads Pelican releases and would replace Shoebill.
```bash
cd /var/www/shoebill
php artisan down
git pull
composer install --no-dev --optimize-autoloader
yarn install --frozen-lockfile && yarn build
php artisan migrate --force
php artisan optimize:clear
chown -R www-data:www-data /var/www/shoebill
php artisan queue:restart
php artisan up
```

## Local development
```bash
composer install && yarn install && yarn build
cp .env.example .env && php artisan key:generate
# .env: APP_ENV=local, APP_DEBUG=true, APP_URL=http://localhost:8000, APP_INSTALLED=true,
#       DB_CONNECTION=sqlite, DB_DATABASE=/absolute/path/to/database/database.sqlite
touch database/database.sqlite && php artisan migrate --seed
php artisan p:user:make
php artisan serve            # http://localhost:8000
php artisan schedule:work    # in a second terminal, records stats every minute
```
Create a node with **Test mode** on to try servers without Wings.

Tests and formatting:
```bash
php -d memory_limit=2G vendor/bin/pest
vendor/bin/pint
```

## Troubleshooting
| Problem | Fix |
|---|---|
| Status page stays empty | The scheduler isn't running (step 8), or the server is offline. Samples are taken each minute while running |
| No player count | The egg's game has no supported query; set *Player query* on the egg, and add a `QUERY_PORT` variable if the game uses a separate query port |
| Crash emails never arrive | Queue worker not running (step 9) or mail not configured |
| "Could not connect to the node" | Wings is down, the FQDN/port is wrong, or SSL settings don't match. Check `journalctl -u wings` on the node |
| 500 error after an update | `php artisan optimize:clear`, check `storage/logs/laravel.log`, and fix permissions (step 6) |
| Out of memory while running tests | Run Pest with `php -d memory_limit=2G` |

## Contact
- Maintainer: <concat@windspace.dev>
- Questions and ideas: [GitHub Discussions](https://github.com/yamashi8040-ai/Shoebill/discussions) · Bugs: [Issues](https://github.com/yamashi8040-ai/Shoebill/issues) · Security: [security.md](security.md)

## Credits and license
Shoebill is built on:
- [Pelican Panel](https://github.com/pelican-dev/panel) © Pelican contributors, **AGPL-3.0**
- [Pterodactyl Panel](https://github.com/pterodactyl/panel) © Dane Everitt and contributors, **MIT** (notice kept in [`licenses/PTERODACTYL-MIT.md`](licenses/PTERODACTYL-MIT.md))

Shoebill as a whole is licensed under the **[GNU AGPL-3.0](license)**. If you run a modified version for other people,
you must offer them its source code: publish it and set `SHOEBILL_SOURCE_URL`. Shoebill is not affiliated with or
endorsed by either project. "Pterodactyl" is a registered trademark of its owner. See [NOTICE.md](NOTICE.md) and the
in-panel `/credits` page.
