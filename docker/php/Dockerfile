FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    git curl zip unzip default-mysql-client libonig-dev libxml2-dev libzip-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath zip


# instala composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www
