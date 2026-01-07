FROM php:8.2-fpm

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev libxml2-dev zip unzip

# Instalar extensiones PHP
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Obtener Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# COPIAR PRIMERO LOS ARCHIVOS DE COMPOSER (Esto ayuda a ver el error real)
COPY composer.json composer.lock ./

# Instalar dependencias (Sin el flag --no-dev temporalmente para ver errores detallados)
RUN composer install --no-interaction --optimize-autoloader --no-scripts

# Copiar el resto de la aplicación
COPY . .

# Permisos
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]