# syntax=docker/dockerfile:1.6

############################
# Composer install (production)
############################
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader

############################
# Build assets (Vite + React)
############################
FROM node:22-alpine AS assets
WORKDIR /app
# Bring in vendor dir first so npm can resolve file: paths into vendor/forjedio
COPY --from=vendor /app/vendor/forjedio ./vendor/forjedio
COPY package.json package-lock.json* ./
RUN npm ci --ignore-scripts --include=optional
COPY vite.config.ts tsconfig.json components.json ./
COPY resources ./resources
RUN npm run build

############################
# Runtime image
############################
FROM php:8.4-fpm-alpine AS runtime

# System packages: nginx, supervisor, sqlite client, image libs for thumbnails
RUN apk add --no-cache \
        nginx \
        supervisor \
        sqlite \
        sqlite-libs \
        tini \
        bash \
        curl \
        oniguruma-dev \
        libpng-dev \
        libjpeg-turbo-dev \
        libwebp-dev \
        freetype-dev \
        zlib-dev \
        icu-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_sqlite \
        gd \
        bcmath \
        intl \
        opcache \
    && apk del --no-cache \
        oniguruma-dev libpng-dev libjpeg-turbo-dev libwebp-dev freetype-dev zlib-dev icu-dev \
    && rm -rf /var/cache/apk/*

# PHP config
COPY docker/php/php.ini /usr/local/etc/php/conf.d/zz-app.ini
COPY docker/php/php-fpm.conf /usr/local/etc/php-fpm.d/zz-app.conf

# Nginx + supervisor config
COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisor/supervisord.conf /etc/supervisord.conf

WORKDIR /app

# Bring in the rest of the app source
COPY . .

# Bring in composer-installed vendor dir
COPY --from=vendor /app/vendor ./vendor

# Bring in compiled assets
COPY --from=assets /app/public/build ./public/build

# Entrypoint
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Persistent data dir
RUN mkdir -p /data/storage /data/storage/galleries \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs \
    && chown -R www-data:www-data /app /data \
    && chmod -R 775 storage bootstrap/cache /data

EXPOSE 80

ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr \
    DB_CONNECTION=sqlite \
    DB_DATABASE=/data/database.sqlite \
    FILESYSTEM_DISK=local \
    SESSION_DRIVER=database \
    QUEUE_CONNECTION=database \
    CACHE_STORE=database \
    APP_URL=http://localhost

ENTRYPOINT ["/sbin/tini", "--", "/entrypoint.sh"]
CMD ["supervisord", "-c", "/etc/supervisord.conf"]
