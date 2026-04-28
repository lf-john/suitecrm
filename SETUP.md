# SuiteCRM v8 - New Server Setup Guide

## Prerequisites

- Docker and Docker Compose installed
- SSH access to `git@github.com:lf-john/suitecrm.git`
- The **zip file** containing:
  - `.env` — SuiteCRM environment configuration
  - `upload/` — uploaded documents and files

## Quick Setup (Automated)

```bash
# 1. Clone the repository
git clone git@github.com:lf-john/suitecrm.git
cd suitecrm

# 2. Extract the zip contents to a folder
unzip /path/to/crm-files.zip -d ~/crm-files

# 3. Run the setup script with your server's hostname
bash setup.sh ~/crm-files crm.yourserver.com
```

The script handles everything: config updates, Docker startup, database import, file restoration, cache clearing, and permission fixes.

**Default login:** `admin` / `admin` — **change this immediately after first login.**

---

## Manual Setup (Step by Step)

Use this if you need more control or if the script fails at any step.

### Step 1: Clone the Repository

```bash
git clone git@github.com:lf-john/suitecrm.git
cd suitecrm
```

### Step 2: Install the Environment File

Copy the `.env` file from the zip into the **`suitecrm/`** subdirectory (not the project root):

```bash
cp /path/to/zip/.env ./suitecrm/.env
```

> **Why this matters:** SuiteCRM is a Symfony app. The `.env` must be at `suitecrm/.env` where the Symfony framework reads it. Placing it in the project root will have no effect.

### Step 3: Update Hostname

The config files ship with `crm3.logicalfront.com` hardcoded. You must update **all four locations** to match your server's hostname. Replace `YOUR_HOSTNAME` below:

**a) `suitecrm/.env`** — Update `SITE_URL` and `CORS_ALLOW_ORIGIN`:
```
SITE_URL="http://YOUR_HOSTNAME:8088"
CORS_ALLOW_ORIGIN='^https?://(localhost|127\.0\.0\.1|YOUR_HOSTNAME)(:[0-9]+)?$'
```

**b) `docker/nginx/conf.d/suitecrm.conf`** — Update `server_name`:
```nginx
server_name YOUR_HOSTNAME localhost;
```

**c) `suitecrm/public/legacy/config.php`** — Update both values:
```php
'host_name' => 'YOUR_HOSTNAME',
'site_url' => 'http://YOUR_HOSTNAME:8088',
```

**d) `config/suitecrm/env.local`** — Update `SITE_URL` and `CORS_ALLOW_ORIGIN`:
```
SITE_URL=http://YOUR_HOSTNAME:8088
CORS_ALLOW_ORIGIN=^https?://(localhost|127\.0\.0\.1|YOUR_HOSTNAME)(:[0-9]+)?$
```

> **Why this matters:** SuiteCRM's Angular frontend makes API calls to the SITE_URL. If the hostname is wrong, the browser will either fail to connect or be blocked by CORS.

### Step 4: Start Docker Containers

```bash
docker compose up -d
```

If you get a network subnet conflict error (`subnet: 172.20.0.0/16`), edit `docker-compose.yml` and change the subnet to an unused range (e.g., `172.25.0.0/16`).

### Step 5: Wait for the Database

The MySQL container needs ~30 seconds to initialize. Wait for it:

```bash
# Poll until ready (will print "mysqld is alive" when done)
docker exec suitecrm_db mysqladmin ping -u root -prootpassword --wait=30
```

### Step 6: Import the Database

```bash
cat suitecrm_initial_migration.sql | docker exec -i suitecrm_db mysql -u root -prootpassword suitecrm_db
```

> **Important:** Do NOT run this before Step 5 completes. The database must be fully initialized first.

### Step 6b: Create LF Custom Tables

After the main import, run the LF Weekly Plan schema migration. This creates all custom `lf_*` tables and the `opportunity_weekly_snapshot` table. The statements use `CREATE TABLE IF NOT EXISTS` so they are safe to re-run.

```bash
docker exec -i suitecrm_db mysql -u root -prootpassword suitecrm_db \
  < migrations/001_lf_weekly_plan_schema.sql
```

If you are **updating an existing installation** (not doing a fresh install), run only this step — not the full Step 6 import.

### Step 7: Restore Uploaded Files

The `docker-compose.yml` uses a **named Docker volume** for uploads. Copying files to `suitecrm/upload/` on the host filesystem will NOT work — the named volume mounts on top of it.

You must copy files **into the running container**:

```bash
docker cp /path/to/zip/upload/. suitecrm_app:/var/www/html/upload/
docker exec suitecrm_app chown -R www-data:www-data /var/www/html/upload
```

> **Why this matters:** Docker named volumes take precedence over bind mounts at the same path. The volume starts empty and hides any files in the host's `suitecrm/upload/` directory.

### Step 8: Clear Cache and Fix Permissions

```bash
# Create required directories
docker exec suitecrm_app mkdir -p /var/www/html/public/extensions

# Clear and rebuild cache with the new hostname
docker exec suitecrm_app rm -rf /var/www/html/cache/prod
docker exec suitecrm_app php /var/www/html/bin/console cache:clear --env=prod

# Fix ownership (PHP-FPM runs as www-data)
docker exec suitecrm_app chown -R www-data:www-data /var/www/html/cache
docker exec suitecrm_app chown -R www-data:www-data /var/www/html/public/extensions
```

> **Why this matters:** Symfony caches configuration including the SITE_URL. Without clearing the cache, the old hostname remains active. The `public/extensions` directory must exist or cache warming will fail.

### Step 9: Verify and Login

Open `http://YOUR_HOSTNAME:8088` in your browser.

- **Username:** `admin`
- **Password:** `admin`
- **Change the password immediately** — Admin icon (top right) > Profile > Password tab

---

## Troubleshooting

### Login shows "missing credentials" or spins forever
- Verify all four hostname locations were updated (Step 3)
- Clear the Symfony cache (Step 8)
- Check browser dev tools Network tab for CORS errors

### Homepage shows 403 Forbidden
- Verify nginx config has `root /var/www/html/public;` (with `/public`)
- Reload nginx: `docker exec suitecrm_nginx nginx -s reload`

### Database import fails with "connection refused"
- Wait longer after `docker compose up -d` — the DB needs time to initialize
- Check: `docker compose ps` — the `suitecrm_db` container should show `healthy`

### Uploaded documents/images are missing
- Files must be copied into the container, not just the host filesystem (Step 7)
- Verify: `docker exec suitecrm_app ls /var/www/html/upload/ | head`

### Rate limiting locks you out after failed login attempts
- Default: 5 attempts per 30 minutes per username+IP
- Wait 30 minutes, or clear the cache (which resets rate limit counters):
  ```bash
  docker exec suitecrm_app rm -rf /var/www/html/cache/prod
  docker exec suitecrm_app php /var/www/html/bin/console cache:clear --env=prod
  docker exec suitecrm_app chown -R www-data:www-data /var/www/html/cache
  ```

---

## Architecture Notes

| Service | Container | Internal Port | External Port |
|---------|-----------|---------------|---------------|
| SuiteCRM (PHP-FPM) | `suitecrm_app` | 9000 | — |
| Nginx | `suitecrm_nginx` | 80 | **8088** |
| MySQL 8.0 | `suitecrm_db` | 3306 | — |
| Redis 7 | `suitecrm_redis` | 6379 | — |

- The app container runs as root but PHP-FPM workers run as `www-data`
- The Symfony `.env.local` file overrides values in `.env` — check both if config seems wrong
- SuiteCRM uses a double-hash for passwords: `bcrypt(md5(plaintext))` — standard bcrypt hashes will NOT work
