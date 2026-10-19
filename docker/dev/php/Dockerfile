FROM php:8.3-cli-bookworm

WORKDIR /var/www/html

RUN apt-get update \
    && apt-get install --no-install-recommends -y git libonig-dev libsqlite3-dev libzip-dev unzip \
    && docker-php-ext-install mbstring pdo_mysql pdo_sqlite zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
COPY docker/dev/php/entrypoint.sh /usr/local/bin/kp-search-entrypoint

RUN chmod +x /usr/local/bin/kp-search-entrypoint
