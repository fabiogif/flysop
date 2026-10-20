#!/usr/bin/env bash
# Certificado CA do Postgres (Aiven): storage/certs/ fica fora do git, então o deploy pelo
# GitHub Actions não o teria. Vem do secret AIVEN_CA_CERT (conteúdo do .pem) e é gravado no
# caminho que DB_SSLROOTCERT aponta. Roda antes de caches.sh (ordem alfabética) e também na
# máquina do release_command (o entrypoint executa os scripts antes do comando).

if [ -z "$AIVEN_CA_CERT" ]; then
    exit 0
fi

target="/var/www/html/${DB_SSLROOTCERT:-storage/certs/aiven-ca.pem}"
mkdir -p "$(dirname "$target")"
printf '%s\n' "$AIVEN_CA_CERT" > "$target"
chmod 644 "$target"
