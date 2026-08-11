# fly.io / deploy – imagem oficial PHP (Debian), evita 404 em apt
# Para nginx+php-fpm local use: Dockerfile.nginx

FROM php:8.2-cli AS base

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libzip-dev \
        libpng-dev \
        libjpeg-dev \
        libonig-dev \
        libxml2-dev \
        libpq-dev \
        curl \
    && docker-php-ext-configure gd --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        zip \
        pdo \
        pdo_pgsql \
        mbstring \
        xml \
        bcmath \
        gd \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Producao serve via "php artisan serve" (SAPI cli) — o opcache do PHP vem habilitado
# por padrao na imagem so para a SAPI web (opcache.enable_cli=Off), entao toda request
# recompilava o framework inteiro do zero a cada acesso (achado ao investigar tempo de
# resposta de ~2.5s mesmo com a maquina ja "quente"). memory_consumption reduzido de
# 128 (padrao) para 64MB porque a maquina de producao tem so 256MB de RAM no total.
RUN { \
        echo 'opcache.enable=1'; \
        echo 'opcache.enable_cli=1'; \
        echo 'opcache.memory_consumption=64'; \
        echo 'opcache.max_accelerated_files=10000'; \
        echo 'opcache.validate_timestamps=0'; \
    } > /usr/local/etc/php/conf.d/zz-opcache-cli.ini

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

COPY . .
RUN composer dump-autoload --optimize \
    && composer run-script post-install-cmd 2>/dev/null || true

RUN chown -R www-data:www-data /app \
    && chmod -R 755 /app/storage /app/bootstrap/cache

ENV PORT=8080
EXPOSE 8080

CMD ["sh", "-c", "php artisan config:cache && php artisan route:cache && php artisan view:cache && php artisan serve --host=0.0.0.0 --port=${PORT}"]
