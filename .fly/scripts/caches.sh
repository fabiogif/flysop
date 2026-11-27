#!/usr/bin/env bash

# route:cache e view:cache já vêm prontos da imagem (Dockerfile); só a config depende dos
# secrets de produção e precisa ser gerada no boot.
/usr/bin/php /var/www/html/artisan config:cache --no-ansi -q
