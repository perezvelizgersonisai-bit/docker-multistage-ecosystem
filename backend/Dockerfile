# ==============================================================================
# Dockerfile Multi-stage para Backend Laravel 12 API (PHP 8.2 FPM Alpine)
# Optimización de Capas, Cache de Dependencias y Seguridad de Producción
# ==============================================================================

# --- Etapa 1: Constructor de Dependencias (composer-builder) ---
FROM composer:2 AS composer-builder
WORKDIR /app

# Copiar manifiestos de Composer para aprovechar la caché de capas de Docker
COPY composer.json composer.lock ./

# Instalar dependencias de producción de Composer sin ejecutar scripts dev
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-progress \
    --ignore-platform-reqs

# --- Etapa 2: Imagen Final de Producción (runtime) ---
FROM php:8.2-fpm-alpine AS production

LABEL maintainer="Kodigo DevOps Team <devops@kodigo.org>"
LABEL description="Laravel API Backend - Optimized Multi-stage Alpine Image"

WORKDIR /var/www/html

# Instalar dependencias del sistema y bibliotecas requeridas para extensiones PHP
# Se agrupan comandos en una sola capa RUN y se purga el cache de apk inmediatamente
RUN apk add --no-cache \
    curl \
    libzip-dev \
    libpng-dev \
    libxml2-dev \
    oniguruma-dev \
    mysql-client \
    fcgi \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        xml \
        ctype \
        bcmath \
        opcache \
    && rm -rf /tmp/* /var/cache/apk/*

# Configuración de rendimiento de PHP Opcache para producción
RUN { \
    echo 'opcache.enable=1'; \
    echo 'opcache.memory_consumption=128'; \
    echo 'opcache.interned_strings_buffer=8'; \
    echo 'opcache.max_accelerated_files=10000'; \
    echo 'opcache.revalidate_freq=0'; \
    echo 'opcache.validate_timestamps=0'; \
    echo 'opcache.save_comments=1'; \
    echo 'opcache.fast_shutdown=1'; \
} > /usr/local/etc/php/conf.d/opcache-recommended.ini

# Parámetros operativos optimizados de PHP
RUN { \
    echo 'memory_limit = 256M'; \
    echo 'upload_max_filesize = 20M'; \
    echo 'post_max_size = 25M'; \
    echo 'max_execution_time = 60'; \
    echo 'expose_php = Off'; \
} > /usr/local/etc/php/conf.d/custom.ini

# Copiar el código fuente de la aplicación
COPY . /var/www/html

# Copiar el directorio de vendor empaquetado en la etapa 1
COPY --from=composer-builder /app/vendor /var/www/html/vendor

# Asegurar directorios de almacenamiento y caché con permisos para www-data
RUN mkdir -p /var/www/html/storage/framework/{sessions,views,cache} \
             /var/www/html/storage/logs \
             /var/www/html/bootstrap/cache \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Configurar script de inicio del contenedor
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Exponer el puerto estándar de PHP-FPM
EXPOSE 9000

# Healthcheck nativo para verificar que el servicio php-fpm responda
HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
  CMD pidof php-fpm || exit 1

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["php-fpm"]
