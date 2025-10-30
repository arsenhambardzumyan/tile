FROM php:8.3-fpm-alpine

# Install system deps
RUN apk add --no-cache bash git unzip icu-dev libxml2-dev libzip-dev oniguruma-dev curl

# PHP extensions
RUN docker-php-ext-install intl pdo pdo_mysql xml zip soap

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Workdir
WORKDIR /var/www/html

# Install Symfony CLI (optional, not required for prod)
RUN curl -sS https://get.symfony.com/cli/installer | bash -s -- --install-dir=/usr/local/bin

# Expose dev server via PHP -S behind nginx or caddy; we will run via nginx container
EXPOSE 8080

