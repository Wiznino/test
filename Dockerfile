FROM node:22-alpine AS frontend

WORKDIR /app

COPY package.json vite.config.js ./
COPY resources ./resources
COPY public ./public

RUN npm install && npm run build

FROM php:8.3-apache

RUN apt-get update && apt-get install -y --no-install-recommends \
    libjpeg62-turbo libonig5 libpng16-16 libpq5 libxml2 libzip4 \
    libjpeg-dev libonig-dev libpng-dev libpq-dev libxml2-dev libzip-dev \
    && docker-php-ext-configure gd --with-jpeg \
    && docker-php-ext-install bcmath exif gd mbstring pcntl pdo_pgsql xml zip \
    && apt-get purge -y --auto-remove libjpeg-dev libonig-dev libpng-dev libpq-dev libxml2-dev libzip-dev \
    && rm -rf /var/lib/apt/lists/* \
    && a2enmod rewrite \
    && sed -ri 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/*.conf \
    && sed -ri 's/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts

COPY . .
COPY --from=frontend /app/public/build ./public/build

RUN composer dump-autoload --no-dev --optimize --no-scripts \
    && php artisan package:discover --ansi \
    && mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chmod -R ug+rwx storage bootstrap/cache \
    && chmod +x docker/render-start.sh

EXPOSE 10000

CMD ["./docker/render-start.sh"]
