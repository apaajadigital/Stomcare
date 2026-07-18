# =====================================================================
#  StomaCare - Docker image UTUH (PHP 8.4 + Python + model ML)
#  Menjalankan Laravel + engine Naive Bayes dalam satu container.
#  Cocok untuk Railway / Render / Fly.io (bukan Vercel).
# =====================================================================
FROM php:8.4-cli-bookworm

# --- System deps + Python ---
RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip ca-certificates \
        libzip-dev libonig-dev libxml2-dev libsqlite3-dev \
        python3 python3-venv python3-pip \
    && docker-php-ext-install pdo pdo_sqlite mbstring zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# --- Composer ---
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . /app

# --- PHP dependencies (production) ---
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress --no-scripts

# --- Python venv + dependency inferensi (pinned utk kompatibilitas pickle) ---
RUN python3 -m venv /opt/venv \
    && /opt/venv/bin/pip install --no-cache-dir --upgrade pip \
    && /opt/venv/bin/pip install --no-cache-dir -r requirements-deploy.txt

# Engine Python dipanggil Laravel lewat path absolut ini
ENV PYTHON_PATH=/opt/venv/bin/python
ENV APP_ENV=production
ENV APP_DEBUG=false
ENV LOG_CHANNEL=stderr

RUN chmod +x docker-entrypoint.sh \
    && mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache storage/logs bootstrap/cache database \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 8080
CMD ["./docker-entrypoint.sh"]
