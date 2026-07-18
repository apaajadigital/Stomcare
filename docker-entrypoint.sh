#!/usr/bin/env bash
# Entrypoint container StomaCare.
# TANPA `set -e` agar server SELALU start (healthcheck lolos) meski ada langkah gagal.
cd /app

# Siapkan folder + izin tulis (utk storage & SQLite bila dipakai)
mkdir -p database storage/framework/sessions storage/framework/views storage/framework/cache storage/logs bootstrap/cache
chmod -R 777 database storage bootstrap/cache 2>/dev/null || true
[ -f database/database.sqlite ] || touch database/database.sqlite
chmod 666 database/database.sqlite 2>/dev/null || true

php artisan package:discover --ansi 2>&1 || true

# Migrasi dengan RETRY: jaringan privat Railway ke Postgres kadang belum siap
# saat container baru start, sehingga percobaan pertama bisa gagal connect.
migrated=0
for i in 1 2 3 4 5 6 7 8; do
  if php artisan migrate --force 2>&1; then
    echo "[entrypoint] migrate OK pada percobaan $i"
    migrated=1
    break
  fi
  echo "[entrypoint] migrate belum berhasil (percobaan $i), tunggu 5 dtk..."
  sleep 5
done
[ "$migrated" = "1" ] || echo "[entrypoint] PERINGATAN: migrate gagal setelah 8 percobaan"

# Server harus selalu berjalan pada PORT platform (default 8080)
exec php artisan serve --host=0.0.0.0 --port="${PORT:-8080}"
