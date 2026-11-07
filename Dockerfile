# Stage 1: Build Composer dependencies
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
COPY app ./app
COPY bootstrap ./bootstrap
COPY database ./database
COPY config ./config
RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs --no-scripts

# Stage 2: Build Node assets (kept, but no build output copied)
FROM node:18 AS nodebuild
WORKDIR /app
COPY package.json package-lock.json* ./
COPY resources ./resources
COPY vite.config.js ./
RUN npm install && npm run build

# Stage 3: PHP-FPM + Nginx
FROM php:8.4.27-fpm

# Install required packages
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    unzip \
    git \
    nginx \
    supervisor \
    libonig-dev \
    zlib1g-dev \
 && docker-php-ext-install pdo pdo_pgsql mbstring bcmath zip

WORKDIR /var/www/html

# Copy Laravel app from previous build stage
COPY --from=vendor /app/vendor ./vendor
COPY . .

# Copy Nginx and Supervisor configs
COPY docker/nginx.conf /etc/nginx/conf.d/default.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/run.sh /usr/local/bin/run.sh

RUN chmod +x /usr/local/bin/run.sh

RUN mkdir -p storage bootstrap/cache && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 10000

CMD ["/usr/local/bin/run.sh"]
