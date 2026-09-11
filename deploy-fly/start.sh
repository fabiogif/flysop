#!/bin/sh
set -e

php-fpm -D

# Cachear em background: Laravel funciona sem cache (so um pouco mais lento),
# e isso evita que o proxy do Fly desista de conectar em 0.0.0.0:8080 durante
# os ~15-20s que config:cache/route:cache/view:cache levavam antes do nginx subir.
( php artisan config:cache
  php artisan route:cache
  php artisan view:cache ) &

exec nginx -g 'daemon off;'
