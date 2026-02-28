# SuiteCRM v8 Docker — Production Deployment

## Architecture

| Service | Container | Image | Port |
|---------|-----------|-------|------|
| SuiteCRM v8 (PHP-FPM) | suitecrm_app | suitecrm-docker-suitecrm | 9000 (internal) |
| MySQL 8.0 | suitecrm_db | mysql:8.0 | 3306 (internal) |
| Redis 7 | suitecrm_redis | redis:7-alpine | 6379 (internal) |
| Nginx | suitecrm_nginx | suitecrm-docker-nginx | 80, 443 |

Access: `https://crm.logicalfront.com`

## Volume Strategy

This deployment uses **named Docker volumes** (not bind mounts):

| Volume | Mount Point | Purpose |
|--------|-------------|---------|
| `suitecrm_app_code` (external) | `/var/www/html` | SuiteCRM application code |
| `suitecrm_nginx_confd` (external) | `/etc/nginx/conf.d` | Nginx site config |
| `suitecrm_ssl_certs` (external) | `/etc/nginx/ssl` | SSL certificates |
| `suitecrm_uploads` | `/var/www/html/upload` | User-uploaded files |
| `suitecrm_mysql_data` | `/var/lib/mysql` | Database storage |
| `suitecrm_redis_data` | `/data` | Redis persistence |

The three external volumes must be created before starting the stack. See **Fresh Setup** below.

## Fresh Setup

### Prerequisites
- Docker and Docker Compose
- SSL certificates in `docker/nginx/ssl/`
- A `.env` file (see `.env` section below)

### Steps

```bash
# 1. Clone the repo
git clone git@github.com:lf-john/suitecrm.git /opt/suitecrm
cd /opt/suitecrm

# 2. Create external volumes
docker volume create suitecrm_app_code
docker volume create suitecrm_nginx_confd
docker volume create suitecrm_ssl_certs

# 3. Populate volumes from repo
docker run --rm -v suitecrm_app_code:/vol -v /opt/suitecrm/suitecrm:/src alpine \
  sh -c "cp -a /src/. /vol/"
docker run --rm -v suitecrm_nginx_confd:/vol -v /opt/suitecrm/docker/nginx/conf.d:/src alpine \
  sh -c "cp -a /src/. /vol/"
docker run --rm -v suitecrm_ssl_certs:/vol -v /opt/suitecrm/docker/nginx/ssl:/src alpine \
  sh -c "cp -a /src/. /vol/"

# 4. Start the stack
docker compose up -d --build

# 5. Import database (if restoring from a dump)
docker exec -i suitecrm_db mysql -u root -prootpassword suitecrm_db < /path/to/dump.sql

# 6. Clear cache
docker exec suitecrm_app bash -c "export TMPDIR=/tmp && php -d sys_temp_dir=/tmp bin/console cache:clear --env=prod"
docker exec suitecrm_app chown -R www-data:www-data /var/www/html/cache

# 7. Set up cron (see Cron section below)
```

## The `.env` File

Symfony requires a `.env` file at `/var/www/html/.env` inside the container (which maps to `suitecrm/.env` on disk). This file is **gitignored** but must exist for the application to boot.

Key variables that must be set:
- `DATABASE_URL` — MySQL connection string
- `SITE_URL` — Full URL (e.g., `https://crm.logicalfront.com`)
- `CORS_ALLOW_ORIGIN` — Allowed origins regex
- `APP_SECRET` — Symfony secret key
- `DEPRECATION_LOG_LEVEL`, `SECURITY_LOG_LEVEL` — Set to `error`
- SAML placeholder values (required even when using native auth)

A working `.env` file is saved at `suitecrm/.env` in the repo for reference.

## Deploying Code Changes

Since we use named volumes, file edits on disk don't appear in the container automatically. To deploy changes:

```bash
# Copy changed files into the container
docker cp /opt/suitecrm/suitecrm/public/legacy/custom/. \
  suitecrm_app:/var/www/html/public/legacy/custom/

# Fix permissions
docker exec suitecrm_app chown -R www-data:www-data /var/www/html/public/legacy/custom/

# Clear cache
docker exec suitecrm_app bash -c "export TMPDIR=/tmp && php -d sys_temp_dir=/tmp bin/console cache:clear --env=prod"
docker exec suitecrm_app chown -R www-data:www-data /var/www/html/cache
```

## Cron Jobs

Two cron entries are required on the **host machine** (not inside the container). Add them to root's crontab (`sudo crontab -e`):

```cron
# SuiteCRM scheduler — runs every minute, processes internal scheduled jobs
* * * * * docker exec -u www-data suitecrm_app php /var/www/html/public/legacy/cron.php > /dev/null 2>&1

# Opportunity Weekly Snapshot — Fridays at 9:00 AM Mountain Time (16:00 UTC)
0 16 * * 5 docker exec -u www-data suitecrm_app php /var/www/html/public/legacy/custom/modules/LF_WeeklyPlan/snapshot_cron.php > /dev/null 2>&1
```

The `setup3.sh` script can install these automatically — see the `--cron` option.

**Why host-level cron?** Docker containers don't persist cron entries across rebuilds. Host cron with `docker exec` is simpler, more visible (`crontab -l`), and survives container restarts.

## Common Operations

```bash
# Check container status
docker compose ps

# View logs
docker compose logs -f              # All services
docker logs suitecrm_app            # App only
docker logs suitecrm_nginx          # Nginx only

# Access container shell
docker exec -it suitecrm_app bash

# Database shell
docker exec -it suitecrm_db mysql -u root -prootpassword suitecrm_db

# Restart
docker compose restart

# Stop
docker compose down

# Backup database
docker exec suitecrm_db mysqldump -u root -prootpassword suitecrm_db > backup.sql
```

## Dev Environment Setup

To run a dev replica on a different machine:

1. Clone the repo and follow **Fresh Setup** above
2. Add to `/etc/hosts` on the dev machine: `<machine-ip> crm.logicalfront.com`
3. Add the same hosts entry on your workstation pointing to the dev machine
4. Import a production database dump
5. The hostname, ports, SSL, and all config are identical to production

## Hostname Configuration

The hostname `crm.logicalfront.com` is stored in 9 locations across 5 files. All should match:

| File | Setting |
|------|---------|
| `suitecrm/.env` | `SITE_URL`, `CORS_ALLOW_ORIGIN` |
| `config/suitecrm/env.local` | `SITE_URL`, `CORS_ALLOW_ORIGIN` |
| `docker/nginx/conf.d/suitecrm.conf` | `server_name` |
| `suitecrm/public/legacy/config.php` | `site_url`, `host_name` |
| `suitecrm/public/legacy/config_override.php` | `site_url`, `http_referer.list` |

The `setup3.sh` script handles hostname substitution automatically.
