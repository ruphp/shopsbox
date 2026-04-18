FROM php:8.3-cli-alpine

RUN apk add --no-cache \
        $PHPIZE_DEPS \
        bash \
        git \
        icu-dev \
        libzip-dev \
        postgresql-dev \
        unzip \
    && docker-php-ext-install \
        intl \
        opcache \
        pdo_pgsql \
        zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app/backend
