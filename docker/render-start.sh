#!/bin/sh
set -eu

if [ -n "${RENDER_EXTERNAL_URL:-}" ]; then
    export APP_URL="$RENDER_EXTERNAL_URL"
fi

case "${APP_KEY:-}" in
    base64:*) ;;
    *) export APP_KEY="base64:${APP_KEY:?APP_KEY must be set in Render}" ;;
esac

php artisan migrate --force

if [ -n "${ADMIN_EMAIL:-}" ]; then
    php artisan atu:make-admin "$ADMIN_EMAIL"
fi

render_port="${PORT:-10000}"
sed -i "s/Listen 80/Listen ${render_port}/" /etc/apache2/ports.conf
sed -i "s/*:80>/*:${render_port}>/" /etc/apache2/sites-available/000-default.conf

exec apache2-foreground
