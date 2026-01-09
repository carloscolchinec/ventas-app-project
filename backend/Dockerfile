FROM php:8.2-fpm

# Instalar dependencias del sistema indispensables
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    libicu-dev \
    zlib1g-dev

# Limpiar cache de apt para reducir tamaño de imagen
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar extensiones PHP (Añadidas zip e intl)
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip intl

# Obtener Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copiar archivos de dependencias
COPY composer.json composer.lock* ./

# Ejecutar install con --ignore-platform-reqs para evitar bloqueos por extensiones menores
RUN composer install --no-interaction --optimize-autoloader --no-scripts --ignore-platform-reqs

# Copiar el resto de la aplicación
COPY . .

# Permisos para Laravel
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]