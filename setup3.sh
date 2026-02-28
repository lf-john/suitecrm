#!/usr/bin/env bash
#
# SuiteCRM v8 Docker - New Server Setup Script
#
# Prerequisites:
#   1. Clone the repo:  git clone git@github.com:lf-john/suitecrm.git /opt/suitecrm
#   2. Place the provided zip contents somewhere accessible
#   3. cd /opt/suitecrm && bash setup.sh /path/to/zip/folder [hostname]
#
# The zip folder should contain:
#   - .env                (SuiteCRM environment config)
#   - upload/             (uploaded files/documents)
#
set -euo pipefail

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

info()  { echo -e "${GREEN}[INFO]${NC}  $*"; }
warn()  { echo -e "${YELLOW}[WARN]${NC}  $*"; }
error() { echo -e "${RED}[ERROR]${NC} $*"; exit 1; }

# ─── Validate arguments ─────────────────────────────────────────────
ZIP_DIR="${1:-}"
if [[ -z "$ZIP_DIR" ]]; then
    echo "Usage: bash setup.sh /path/to/zip/folder [hostname]"
    echo ""
    echo "  /path/to/zip/folder  - Directory containing .env and upload/ from the zip"
    echo "  hostname (optional)  - Server hostname (default: localhost)"
    echo ""
    echo "Run from inside the cloned repo (/opt/suitecrm):"
    echo "  bash setup.sh ~/crm-files"
    echo "  bash setup.sh ~/crm-files crm.mycompany.com"
    exit 1
fi

ZIP_DIR="$(realpath "$ZIP_DIR")"
[[ -d "$ZIP_DIR" ]] || error "Directory not found: $ZIP_DIR"
[[ -f "$ZIP_DIR/.env" ]] || error "Missing .env file in $ZIP_DIR"

HOSTNAME="${2:-localhost}"
PORT="8088"
REPO_DIR="$(pwd)"

# Verify we're inside the cloned repo
if [[ ! -f "$REPO_DIR/docker-compose.yml" ]] && [[ ! -f "$REPO_DIR/docker-compose.yaml" ]] && [[ ! -f "$REPO_DIR/compose.yml" ]] && [[ ! -f "$REPO_DIR/compose.yaml" ]]; then
    error "No docker-compose file found in $(pwd).
  Run this script from inside the cloned repo directory.
  Example:
    cd /opt/suitecrm
    bash $0 $*"
fi

info "Repo directory:  $REPO_DIR"
info "Zip contents:    $ZIP_DIR"
info "Hostname:        $HOSTNAME"
info "Port:            $PORT"
echo ""

# ─── Pre-flight checks ──────────────────────────────────────────────
command -v docker >/dev/null 2>&1 || error "Docker is not installed"
docker info >/dev/null 2>&1 || error "Docker is not running (or current user lacks permissions)"
command -v docker compose version >/dev/null 2>&1 || command -v docker-compose >/dev/null 2>&1 || error "Docker Compose is not installed"

# Check for port conflict
if ss -tlnp 2>/dev/null | grep -q ":${PORT} " || netstat -tlnp 2>/dev/null | grep -q ":${PORT} "; then
    warn "Port $PORT is already in use. If this is a previous SuiteCRM instance, stop it first."
    read -rp "Continue anyway? (y/N): " confirm
    [[ "$confirm" =~ ^[Yy]$ ]] || exit 0
fi

# ─── Step 1: Copy .env to the correct location ──────────────────────
info "Step 1/9: Installing .env file..."

cp "$ZIP_DIR/.env" "$REPO_DIR/suitecrm/.env"
info "  Copied .env to suitecrm/.env"

# ─── Step 2: Update hostname in all config files ────────────────────
info "Step 2/9: Updating hostname to '$HOSTNAME' in all config files..."

# suitecrm/.env - SITE_URL and CORS
sed -i "s|SITE_URL=.*|SITE_URL=\"http://${HOSTNAME}:${PORT}\"|" "$REPO_DIR/suitecrm/.env"
# Update CORS to include the new hostname (escape dots for regex)
HOSTNAME_ESCAPED=$(echo "$HOSTNAME" | sed 's/\./\\\\./g')
sed -i "s|CORS_ALLOW_ORIGIN=.*|CORS_ALLOW_ORIGIN='^https?://(localhost\|127\\\\.0\\\\.0\\\\.1\|${HOSTNAME_ESCAPED})(:[0-9]+)?\$'|" "$REPO_DIR/suitecrm/.env"
info "  Updated suitecrm/.env (SITE_URL + CORS)"

# suitecrm/.env.local - SITE_URL if present
if grep -q "SITE_URL" "$REPO_DIR/suitecrm/.env.local" 2>/dev/null; then
    sed -i "s|SITE_URL=.*|SITE_URL=http://${HOSTNAME}:${PORT}|" "$REPO_DIR/suitecrm/.env.local"
    info "  Updated suitecrm/.env.local"
fi

# config/suitecrm/env.local - SITE_URL and CORS if present
if [[ -f "$REPO_DIR/config/suitecrm/env.local" ]]; then
    sed -i "s|SITE_URL=.*|SITE_URL=http://${HOSTNAME}:${PORT}|" "$REPO_DIR/config/suitecrm/env.local"
    HOSTNAME_ESCAPED_SINGLE=$(echo "$HOSTNAME" | sed 's/\./\\./g')
    sed -i "s|CORS_ALLOW_ORIGIN=.*|CORS_ALLOW_ORIGIN=^https?://(localhost\|127\\.0\\.0\\.1\|${HOSTNAME_ESCAPED_SINGLE})(:[0-9]+)?\$|" "$REPO_DIR/config/suitecrm/env.local"
    info "  Updated config/suitecrm/env.local"
fi

# nginx server_name
sed -i "s|server_name .*;|server_name ${HOSTNAME} localhost;|" "$REPO_DIR/docker/nginx/conf.d/suitecrm.conf"
info "  Updated nginx server_name"

# legacy config.php - site_url and host_name
LEGACY_CONFIG="$REPO_DIR/suitecrm/public/legacy/config.php"
if [[ -f "$LEGACY_CONFIG" ]]; then
    sed -i "s|'host_name' => '.*'|'host_name' => '${HOSTNAME}'|" "$LEGACY_CONFIG"
    sed -i "s|'site_url' => '.*'|'site_url' => 'http://${HOSTNAME}:${PORT}'|" "$LEGACY_CONFIG"
    info "  Updated legacy config.php (host_name + site_url)"
fi

# ─── Step 3: Handle Docker network subnet conflict ──────────────────
info "Step 3/9: Checking Docker network availability..."

if docker network ls --format '{{.Name}}' | grep -q "suitecrm"; then
    warn "  Existing SuiteCRM Docker network found. Will reuse."
fi

# ─── Step 4: Start containers ───────────────────────────────────────
info "Step 4/9: Starting Docker containers..."

cd "$REPO_DIR"
docker compose up -d 2>&1 | sed 's/^/  /'

# ─── Step 5: Wait for database ──────────────────────────────────────
info "Step 5/9: Waiting for database to be ready..."

MAX_WAIT=60
WAITED=0
until docker exec suitecrm_db mysqladmin ping -u root -prootpassword --silent 2>/dev/null; do
    WAITED=$((WAITED + 2))
    if [[ $WAITED -ge $MAX_WAIT ]]; then
        error "Database did not become ready within ${MAX_WAIT} seconds"
    fi
    echo -n "."
    sleep 2
done
echo ""
info "  Database is ready (waited ${WAITED}s)"

# ─── Step 6: Import database ────────────────────────────────────────
info "Step 6/9: Importing database..."

SQL_FILE="$REPO_DIR/suitecrm_initial_migration.sql"
[[ -f "$SQL_FILE" ]] || error "SQL file not found: $SQL_FILE"

# Check if tables already exist
TABLE_COUNT=$(docker exec suitecrm_db mysql -u root -prootpassword suitecrm_db -N -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'suitecrm_db';" 2>/dev/null || echo "0")

if [[ "$TABLE_COUNT" -gt 10 ]]; then
    warn "  Database already has $TABLE_COUNT tables."
    read -rp "  Overwrite with fresh import? (y/N): " confirm
    if [[ ! "$confirm" =~ ^[Yy]$ ]]; then
        info "  Skipping database import"
    else
        docker exec suitecrm_db mysql -u root -prootpassword suitecrm_db -e "SET FOREIGN_KEY_CHECKS=0;" 2>/dev/null
        cat "$SQL_FILE" | docker exec -i suitecrm_db mysql -u root -prootpassword suitecrm_db 2>&1 | tail -5
        info "  Database imported successfully"
    fi
else
    cat "$SQL_FILE" | docker exec -i suitecrm_db mysql -u root -prootpassword suitecrm_db 2>&1 | tail -5
    info "  Database imported successfully"
fi

# Update site_url in the database to match new hostname
docker exec suitecrm_db mysql -u root -prootpassword suitecrm_db -e \
    "UPDATE config SET value = 'http://${HOSTNAME}:${PORT}' WHERE category = 'system' AND name = 'site_url';" 2>/dev/null || true

# ─── Step 7: Copy uploads into the named volume ─────────────────────
info "Step 7/9: Restoring uploaded files..."

UPLOAD_SRC="$ZIP_DIR/upload"
if [[ -d "$UPLOAD_SRC" ]] && [[ "$(ls -A "$UPLOAD_SRC" 2>/dev/null)" ]]; then
    # Copy into the running container (the named volume is mounted there)
    docker cp "$UPLOAD_SRC/." suitecrm_app:/var/www/html/upload/
    docker exec suitecrm_app chown -R www-data:www-data /var/www/html/upload
    UPLOAD_COUNT=$(docker exec suitecrm_app find /var/www/html/upload -type f | wc -l)
    info "  Restored $UPLOAD_COUNT files to upload volume"
else
    warn "  No upload directory found in $ZIP_DIR (or it's empty). Skipping."
fi

# ─── Step 8: Clear cache and fix permissions ─────────────────────────
info "Step 8/9: Clearing cache and fixing permissions..."

docker exec suitecrm_app rm -rf /var/www/html/cache/prod
docker exec suitecrm_app mkdir -p /var/www/html/public/extensions
docker exec suitecrm_app php /var/www/html/bin/console cache:clear --env=prod 2>&1 | grep -E "OK|error" | sed 's/^/  /'
docker exec suitecrm_app chown -R www-data:www-data /var/www/html/cache
docker exec suitecrm_app chown -R www-data:www-data /var/www/html/public/extensions
info "  Cache cleared and permissions fixed"

# ─── Done ────────────────────────────────────────────────────────────
echo ""
echo "=========================================="
echo -e "${GREEN}  SuiteCRM setup complete!${NC}"
echo "=========================================="
echo ""
echo "  URL:      http://${HOSTNAME}:${PORT}"
echo "  Login:    admin / admin"
echo ""
echo -e "  ${RED}IMPORTANT: Change the admin password immediately after first login.${NC}"
echo -e "  ${RED}           Go to: Admin icon (top right) > Profile > Password tab${NC}"
echo ""
echo "  To check status:  docker compose ps"
echo "  To view logs:     docker compose logs -f"
echo "  To stop:          docker compose down"
echo ""

# ─── Step 9: Set up cron jobs ─────────────────────────────────────────
info "Step 9/9: Setting up cron jobs..."

CRON_ENTRY_SCHEDULER="* * * * * docker exec -u www-data suitecrm_app php /var/www/html/public/legacy/cron.php > /dev/null 2>&1"
CRON_ENTRY_SNAPSHOT="0 16 * * 5 docker exec -u www-data suitecrm_app php /var/www/html/public/legacy/custom/modules/LF_WeeklyPlan/snapshot_cron.php > /dev/null 2>&1"

CRON_ADDED=0

# Check and add SuiteCRM scheduler cron
if ! crontab -l 2>/dev/null | grep -q "suitecrm_app.*cron.php"; then
    (crontab -l 2>/dev/null; echo "# SuiteCRM scheduler (runs every minute)"; echo "$CRON_ENTRY_SCHEDULER") | crontab -
    info "  Added SuiteCRM scheduler cron (every minute)"
    CRON_ADDED=$((CRON_ADDED + 1))
else
    info "  SuiteCRM scheduler cron already exists, skipping"
fi

# Check and add snapshot cron
if ! crontab -l 2>/dev/null | grep -q "suitecrm_app.*snapshot_cron.php"; then
    (crontab -l 2>/dev/null; echo "# Opportunity Weekly Snapshot (Fridays 9AM Mountain / 16:00 UTC)"; echo "$CRON_ENTRY_SNAPSHOT") | crontab -
    info "  Added snapshot cron (Fridays at 16:00 UTC)"
    CRON_ADDED=$((CRON_ADDED + 1))
else
    info "  Snapshot cron already exists, skipping"
fi

if [[ $CRON_ADDED -gt 0 ]]; then
    info "  $CRON_ADDED cron entries added. Verify with: crontab -l"
else
    info "  All cron entries already configured"
fi
