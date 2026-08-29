FROM node:22-alpine AS frontend

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY vite.config.js ./
COPY resources ./resources

RUN mkdir -p public
RUN npm run build

FROM php:8.4-apache-bookworm
 
ENV APP_ENV=production \
  APP_DEBUG=false \
  LOG_CHANNEL=stderr \
  APACHE_DOCUMENT_ROOT=/var/www/html/public
 
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libicu-dev \
        libonig-dev \
        libpq-dev \
        libxml2-dev \
        libzip-dev \
        unzip \
    && docker-php-ext-install -j"$(nproc)" \
        dom \
        intl \
        mbstring \
        opcache \
        pdo_pgsql \
        zip \
    && a2enmod rewrite \
    && sed -ri 's/^Listen 80$/Listen 10000/' /etc/apache2/ports.conf \
    && rm -rf /var/lib/apt/lists/*
 
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
 
WORKDIR /var/www/html
 
COPY composer.json composer.lock ./
 
RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-progress \
    --no-scripts \
    --no-autoloader
 
COPY . .

RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-progress \
    --classmap-authoritative
 
COPY --from=frontend /app/public/build ./public/build
 
COPY deploy/render-free/apache-vhost.conf /etc/apache2/sites-available/000-default.conf
COPY deploy/render-free/start.sh /usr/local/bin/render-start
 
RUN chmod +x /usr/local/bin/render-start \
  && mkdir -p \
      storage/framework/cache \
      storage/framework/sessions \
      storage/framework/views \
      storage/logs \
      bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache
 
EXPOSE 10000
 
CMD ["/usr/local/bin/render-start"]