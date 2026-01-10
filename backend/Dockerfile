FROM php:8.2-apache

# Instalar dependencias de PHP y MySQL
RUN apt-get update && apt-get install -y \
    libpng-dev \
    zip \
    libzip-dev \
    && docker-php-ext-install pdo_mysql gd zip

# Habilitar el módulo rewrite de Apache para Laravel
RUN a2enmod rewrite

# Configurar Apache para que use /var/www/html/public como raíz
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

WORKDIR /var/www/html

# Copiar archivos y dar permisos a carpetas de Laravel
COPY . .
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache