# =====================================================================
#  StomaCare - Docker image UTUH (Python 3.12 + PHP 8.4 + model ML)
#  Base = python:3.12 resmi (agar numpy/scipy/scikit-learn versi baru
#  tersedia & cocok dengan versi saat model dilatih -> pickle aman),
#  PHP 8.4 dipasang dari repo Sury.
#  Cocok untuk Railway / Render / Fly.io.
# =====================================================================
FROM python:3.12-slim-bookworm

# --- PHP 8.4 (Sury) + lib runtime untuk scientific stack ---
RUN apt-get update && apt-get install -y --no-install-recommends \
        ca-certificates curl gnupg unzip git libgomp1 \
    && curl -sSL https://packages.sury.org/php/apt.gpg -o /etc/apt/trusted.gpg.d/sury-php.gpg \
    && echo "deb https://packages.sury.org/php/ bookworm main" > /etc/apt/sources.list.d/sury-php.list \
    && apt-get update && apt-get install -y --no-install-recommends \
        php8.4-cli php8.4-sqlite3 php8.4-mbstring php8.4-xml php8.4-curl \
        php8.4-zip php8.4-bcmath php8.4-pgsql \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# --- Composer ---
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . /app

# --- PHP dependencies (production) ---
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress --no-scripts

# --- Python deps inferensi (Python 3.12 -> numpy 2.5.1 dll tersedia) ---
RUN pip install --no-cache-dir -r requirements-deploy.txt

# Engine Python dipanggil Laravel lewat path absolut ini
ENV PYTHON_PATH=/usr/local/bin/python3 \
    APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr \
    SESSION_DRIVER=file \
    CACHE_STORE=file \
    QUEUE_CONNECTION=sync

RUN chmod +x docker-entrypoint.sh \
    && mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache storage/logs bootstrap/cache database \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 8080
CMD ["./docker-entrypoint.sh"]
