FROM php:8.2-apache

# Instalar extensiones necesarias para Laravel
RUN apt-get update && apt-get install -y \
    libpng-dev \
    zip \
    libzip-dev \
    && docker-php-ext-install pdo_mysql gd zip

# Habilitar mod_rewrite para las rutas de Laravel
RUN a2enmod rewrite

# Cambiar el DocumentRoot de Apache a la carpeta /public de Laravel
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

WORKDIR /var/www/html
COPY . .

# Permisos para Storage y Cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache