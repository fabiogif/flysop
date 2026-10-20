#!/usr/bin/env sh

# Run user scripts, if they exist
for f in /var/www/html/.fly/scripts/*.sh; do
    # Bail out this loop if any script exits with non-zero status code
    bash "$f" || break
done
# Só o que o PHP grava em runtime (o resto já é www-data desde o build): o chown -R do projeto
# inteiro (~18 mil arquivos) atrasava cada boot a frio.
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

if [ $# -gt 0 ]; then
    # If we passed a command, run it as root
    exec "$@"
else
    exec supervisord -c /etc/supervisor/supervisord.conf
fi
