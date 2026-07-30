FROM php:8.1-apache

# Instalar extensiones necesarias para conectar PostgreSQL con PDO
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Copiar tu código de la carpeta src al servidor web
COPY ./src /var/www/html/

# Dar permisos de lectura/escritura
RUN chown -R www-data:www-data /var/www/html