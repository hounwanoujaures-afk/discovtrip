FROM dunglas/frankenphp:php8.4-bookworm

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Node.js
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y nodejs

# Extensions PHP
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

# Dépendances Composer
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

# Dépendances Node
COPY package.json package-lock.json ./
RUN npm ci

# Tout le projet
COPY . .

# Build + caches Laravel
RUN npm run build \
    && mkdir -p storage/framework/{sessions,views,cache,testing} storage/logs bootstrap/cache \
    && chmod -R 777 storage bootstrap/cache \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

EXPOSE 8080

CMD php artisan migrate --force \
    && php artisan storage:link --force \
    && frankenphp run --config /etc/caddy/Caddyfile