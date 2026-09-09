# fly.io / deploy – imagem oficial PHP (Debian), evita 404 em apt
# Para uso local via docker-compose use: Dockerfile.dev

FROM php:8.2-fpm AS base

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        nginx \
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

# opcache.enable_cli=1 evita recompilar o framework do zero nos comandos "php artisan"
# de boot (config:cache/route:cache/view:cache, release_command). memory_consumption
# reduzido de 128 (padrao) para 64MB porque a maquina de producao tem so 256MB de RAM.
RUN { \
        echo 'opcache.enable=1'; \
        echo 'opcache.enable_cli=1'; \
        echo 'opcache.memory_consumption=64'; \
        echo 'opcache.max_accelerated_files=10000'; \
        echo 'opcache.validate_timestamps=0'; \
    } > /usr/local/etc/php/conf.d/zz-opcache.ini

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

COPY . .
RUN composer dump-autoload --optimize \
    && composer run-script post-install-cmd 2>/dev/null || true

RUN chown -R www-data:www-data /app \
    && chmod -R 755 /app/storage /app/bootstrap/cache

# Antes rodava tudo (inclusive JS/CSS/imagens) via "php artisan serve" — servidor de
# dev single-threaded, sem front-end de webserver. Toda requisicao, ate um .svg
# estatico, passava pelo bootstrap completo do Laravel e ficava numa fila serializada
# (~3-6s por asset, medido em producao). Agora nginx serve estatico direto do disco e
# so repassa .php para o php-fpm (pm=ondemand, ver docker/fly/www.conf — poucos
# workers residentes, adequado aos 256MB de RAM da maquina).
COPY deploy-fly/nginx.conf /etc/nginx/sites-enabled/default
COPY deploy-fly/www.conf /usr/local/etc/php-fpm.d/www.conf
COPY deploy-fly/start.sh /start.sh
RUN chmod +x /start.sh

ENV PORT=8080
EXPOSE 8080

CMD ["/start.sh"]
