FROM php:8.2-fpm

# Install dependensi sistem
RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev libxml2-dev zip unzip

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install ekstensi PHP untuk Laravel & MySQL
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Ambil Composer terbaru
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www
COPY . .

# Install dependensi prod
RUN composer install --no-interaction --optimize-autoloader --no-dev

# Port standar PHP-FPM adalah 9000
EXPOSE 9000
CMD ["php-fpm"]