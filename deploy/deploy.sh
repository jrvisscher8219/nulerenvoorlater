#!/usr/bin/env sh
# Eenvoudige deploy naar Strato via lftp
# Vereist: lftp (sudo apt install lftp)
# Gebruik: kopieer deploy/.env.example naar deploy/.env en vul in, daarna:
#   sh deploy/deploy.sh

set -eu

# Laad env
ENV_FILE="$(dirname "$0")/.env"
if [ ! -f "$ENV_FILE" ]; then
  echo "deploy/.env ontbreekt. Kopieer eerst deploy/.env.example naar deploy/.env en vul in." >&2
  exit 1
fi
# shellcheck disable=SC1090
. "$ENV_FILE"

# Validatie
: "${FTP_HOST:?Vul FTP_HOST in in deploy/.env}"
: "${FTP_USER:?Vul FTP_USER in in deploy/.env}"
: "${FTP_PASS:?Vul FTP_PASS in in deploy/.env}"
: "${FTP_TARGET:?Vul FTP_TARGET in in deploy/.env}"

ROOT_DIR="$(cd "$(dirname "$0")/.." && pwd)"

# LFTP sessie
lftp -u "$FTP_USER","$FTP_PASS" "$FTP_HOST" <<EOF
set ftp:ssl-allow true
set ssl:verify-certificate no
set cmd:fail-exit true
set net:max-retries 2
set net:timeout 20

# Ga naar doelmap
cd "$FTP_TARGET"

# 1) Upload alles behalve config.php en niet-webbestanden
mirror --reverse \
  --verbose \
  --delete \
  --exclude-glob .git* \
  --exclude-glob backups/* \
  --exclude-glob docs/* \
  --exclude-glob sql/* \
  --exclude-glob deploy/* \
  --exclude-glob README* \
  --exclude-glob **/*.log \
  --exclude api/config.php \
  "$ROOT_DIR" .

# 2) Upload config.php als laatste (indien lokaal aanwezig)
mkdir -p api
put -O api "$ROOT_DIR/api/config.php"
# Probeer permissies te zetten
chmod 600 api/config.php

bye
EOF

echo "Deploy afgerond. Controleer de site en admin login."
