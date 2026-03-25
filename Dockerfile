FROM dunglas/frankenphp:php8.4-bookworm

# Extensions manquantes (intl, zip) + nécessaires Laravel
RUN install-php-extensions \
    intl \
    zip \
    gd \
    pdo_mysql \
    opcache \
    mbstring \
    xml \
    curl \
    bcmath

WORKDIR /app

# Composer
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

# Node / Vite
COPY package.json package-lock.json ./
RUN npm ci

# Copier tout le projet
COPY . .

# Build assets + caches Laravel
RUN npm run build \
    && mkdir -p storage/framework/{sessions,views,cache,testing} storage/logs bootstrap/cache \
    && chmod -R 777 storage bootstrap/cache \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

EXPOSE 8080

CMD ["php", "artisan", "migrate", "--force", "&&", "php", "artisan", "storage:link", "--force", "&&", "/usr/local/bin/frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]