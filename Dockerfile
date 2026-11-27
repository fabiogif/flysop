# syntax = docker/dockerfile:experimental

# Default to PHP 8.2, but we attempt to match
# the PHP version from the user (wherever `flyctl launch` is run)
# Valid version values are PHP 7.4+
ARG PHP_VERSION=8.2
ARG NODE_VERSION=18
FROM fideloper/fly-laravel:${PHP_VERSION} as base

# PHP_VERSION needs to be repeated here
# See https://docs.docker.com/engine/reference/builder/#understand-how-arg-and-from-interact
ARG PHP_VERSION

LABEL fly_launch_runtime="laravel"

# copy application code, skipping files based on .dockerignore
COPY . /var/www/html

RUN composer install --optimize-autoloader --no-dev \
    && mkdir -p storage/logs \
    && php artisan optimize:clear \
    && chown -R www-data:www-data /var/www/html \
    && echo "MAILTO=\"\"\n* * * * * www-data /usr/bin/php /var/www/html/artisan schedule:run" > /etc/cron.d/laravel \
    && cp .fly/entrypoint.sh /entrypoint \
    && chmod +x /entrypoint

# Sem o sed de TrustProxies do template: app/Http/Middleware/TrustProxies.php já define
# $proxies = '*', e o sed gerava "$proxies = "*" = '*'" (ParseError, derrubou o deploy v57).

# Caches que não dependem dos secrets ficam prontos na imagem: no boot a frio da máquina
# 256MB eles somavam boa parte dos ~60s de espera (config:cache continua no boot, em
# .fly/scripts/caches.sh, porque precisa do .env/secrets de produção).
RUN php artisan route:cache \
    && php artisan view:cache \
    && chown -R www-data:www-data storage bootstrap/cache

# If we're using Octane...
RUN if grep -Fq "laravel/octane" /var/www/html/composer.json; then \
        rm -rf /etc/supervisor/conf.d/fpm.conf; \
        if grep -Fq "spiral/roadrunner" /var/www/html/composer.json; then \
            mv /etc/supervisor/octane-rr.conf /etc/supervisor/conf.d/octane-rr.conf; \
            if [ -f ./vendor/bin/rr ]; then ./vendor/bin/rr get-binary; fi; \
            rm -f .rr.yaml; \
        else \
            mv .fly/octane-swoole /etc/services.d/octane; \
            mv /etc/supervisor/octane-swoole.conf /etc/supervisor/conf.d/octane-swoole.conf; \
        fi; \
        rm /etc/nginx/sites-enabled/default; \
        ln -sf /etc/nginx/sites-available/default-octane /etc/nginx/sites-enabled/default; \
    fi

# Sem etapa de build de assets (Node) do template do fly launch: o CSS/JS compilado já vem
# versionado em public/ — é o que produção sempre serviu — e o Laravel Mix deste projeto
# (webpack antigo) não compila no Node 18 (ERR_OSSL_EVP_UNSUPPORTED).

EXPOSE 8080

ENTRYPOINT ["/entrypoint"]
