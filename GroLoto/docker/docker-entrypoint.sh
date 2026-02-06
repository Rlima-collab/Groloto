#!/usr/bin/env bash
set -euo pipefail

# Minimal entrypoint: wait for DB (if using pgsql), run migrations and warm cache, then start CMD
echo "[entrypoint] starting..."

# Only try to wait for DB if DATABASE_URL contains 'pgsql'
if [[ "${DATABASE_URL:-}" == pgsql:* || "${DATABASE_URL:-}" == postgres:* ]]; then
  echo "[entrypoint] detected PostgreSQL DATABASE_URL, waiting for DB to be ready..."

  # Try pg_isready first
  if command -v pg_isready >/dev/null 2>&1; then
    until pg_isready -h "${POSTGRES_HOST:-db}" -p "${POSTGRES_PORT:-5432}" -U "${POSTGRES_USER:-postgres}" >/dev/null 2>&1; do
      echo "[entrypoint] waiting for postgres..."
      sleep 1
    done
  else
    # Fallback: loop until a simple Doctrine query succeeds
    until php bin/console doctrine:query:sql "SELECT 1" >/dev/null 2>&1; do
      echo "[entrypoint] waiting for postgres (no pg_isready available)..."
      sleep 1
    done
  fi

  echo "[entrypoint] DB is ready — running migrations and warming cache"
  php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration || true
  php bin/console cache:clear --env=prod --no-debug || true
  chown -R www-data:www-data var/
fi

# If DATABASE_URL is sqlite and uses a file path on the host, we don't try to create DB here
# Let app run normally. For safety ensure permissions on var
chown -R www-data:www-data var/ || true

# Exec the container CMD (apache2-foreground)
exec "$@"
