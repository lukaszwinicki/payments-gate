FROM php:8.2-apache

# Ustaw katalog roboczy
WORKDIR /var/www

# Instalacja zależności systemowych i PHP
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim \
    unzip \
    git \
    curl \
    libzip-dev \
    libpq-dev \
    libonig-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

# PHP extensions
RUN docker-php-ext-install pdo pdo_mysql pdo_pgsql mbstring zip exif pcntl

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Skopiuj aplikację
COPY . /var/www

# Zmień DocumentRoot na public/ (Laravel)
RUN sed -i 's|/var/www/html|/var/www/public|g' /etc/apache2/sites-available/000-default.conf \
    && sed -i 's|/var/www/html|/var/www/public|g' /etc/apache2/apache2.conf

# Włącz mod_rewrite (wymagane dla Laravel routes)
RUN a2enmod rewrite

# Zainstaluj zależności aplikacji Laravel
RUN composer install --no-dev --optimize-autoloader

# Ustaw uprawnienia do storage i cache
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache || true

# Ustawienia portu HTTP
EXPOSE 80

# Uruchom migracje i Apache przy starcie
CMD php artisan migrate --force && apache2-foreground
