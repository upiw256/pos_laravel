# Dockerfile untuk Laravel 13 (PHP 8.3/8.4 + Filament)
FROM php:8.3-fpm-alpine

# Set working directory
WORKDIR /var/www/html

# Install dependensi sistem dasar dan ekstensi PHP yang dibutuhkan Laravel & PostgreSQL
RUN apk --no-cache add \
    git \
    curl \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    zip \
    unzip \
    libpq-dev \
    postgresql-dev \
    libzip-dev \
    nodejs \
    npm

# Install PHP extensions
RUN docker-php-ext-install pdo_pgsql pgsql mbstring exif pcntl bcmath gd intl zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Salin semua file dari host ke container
COPY . /var/www/html/

# Atur perizinan (permissions) agar Nginx/PHP-FPM bisa menulis ke folder storage
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

# (Proses npm dan composer install sengaja dilewati di sini karena direktori akan di-mount dari local host via docker-compose)

EXPOSE 9000

# Jalankan PHP-FPM
CMD ["php-fpm"]
