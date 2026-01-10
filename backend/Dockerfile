FROM php:8.2-apache

# 1. Instalar dependencias necesarias
RUN apt-get update && apt-get install -y \
    libpng-dev \
    zip \
    libzip-dev \
    && docker-php-ext-install pdo_mysql gd zip

# 2. Habilitar mod_rewrite para Laravel
RUN a2enmod rewrite

# 3. Forzar la creación del directorio y configurar Apache
RUN mkdir -p /var/www/html/public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

WORKDIR /var/www/html

# 4. Copiar el contenido de la carpeta backend
COPY . .

# 5. Ajustar permisos
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache