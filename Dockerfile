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

# Install dependensi prod (opsional di lokal, wajib di VPS)
RUN composer install --no-interaction --optimize-autoloader --no-dev

# Set concurrent workers untuk php artisan serve agar mendukung multi-user SSE di produksi
ENV PHP_CLI_SERVER_WORKERS=10

EXPOSE 8000
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]