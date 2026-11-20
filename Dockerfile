# syntax=docker/dockerfile:1
#
# BetPulse - imagen PHP-FPM para Laravel + PostgreSQL
# Incluye Composer, así que no necesitas PHP ni Composer en Windows.

FROM php:8.4-fpm-bookworm

# Dependencias del sistema y extensiones PHP
#  - pdo_pgsql / pgsql : driver PostgreSQL
#  - bcmath            : aritmética decimal exacta (saldos, multiplicadores)
#  - intl              : formato de moneda/números
#  - pcntl             : necesario para colas/queue:work y Octane si se usa
RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libpq-dev \
        libicu-dev \
        libzip-dev \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_pgsql \
        pgsql \
        bcmath \
        intl \
        zip \
        opcache \
        pcntl \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Composer (copiado de la imagen oficial)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Configuración PHP para desarrollo
COPY docker/php/php.ini /usr/local/etc/php/conf.d/zz-betpulse.ini
COPY docker/php/fpm-pool.conf /usr/local/etc/php-fpm.d/zzz-betpulse.conf

# Git dentro del contenedor no debe quejarse de la propiedad del bind mount
RUN git config --system --add safe.directory /var/www/html

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_HOME=/tmp/composer

WORKDIR /var/www/html

EXPOSE 9000
# -R permite que los workers corran como root (ver docker/php/fpm-pool.conf, solo dev)
CMD ["php-fpm", "-R"]
