#!/bin/sh
set -e

# Asegurar existencia del archivo .env si no fue montado
if [ ! -f /var/www/html/.env ] && [ -f /var/www/html/.env.example ]; then
    cp /var/www/html/.env.example /var/www/html/.env
fi

# Ajustar permisos en tiempo de arranque para volúmenes persistentes
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true

# Ejecutar el comando principal (php-fpm)
exec "$@"
