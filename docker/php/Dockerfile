FROM php:8.3-cli

RUN apt-get update && apt-get install -y \
    libpq-dev \
    unzip \
    git \
    curl \
    && docker-php-ext-install pdo pdo_pgsql

# instalar redis (phpredis)
RUN pecl install redis \
    && docker-php-ext-enable redis

# instalar pcov (coverage)
RUN pecl install pcov \
    && docker-php-ext-enable pcov

# composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html