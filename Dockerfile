# 1. Partimos de una imagen oficial que incluye PHP 8.3 y servidor Apache
FROM php:8.3-apache

# 2. Instalamos las extensiones necesarias para conectarnos a MySQL
RUN docker-php-ext-install mysqli pdo_mysql

# 3. Copiamos todos los archivos del proyecto a la carpeta raíz de Apache
COPY . /var/www/html/

# 4. Hacemos que Apache escuche en el puerto que Render define dinámicamente
ENV PORT=10000
RUN sed -i 's/80/${PORT}/g' \
    /etc/apache2/sites-available/000-default.conf \
    /etc/apache2/ports.conf
