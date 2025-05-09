FROM php:8.2-cli

# Ustaw katalog roboczy
WORKDIR /var/www

# Instaluj zależności systemowe
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

# Instaluj rozszerzenia PHP
RUN docker-php-ext-install pdo pdo_mysql pdo_pgsql mbstring zip exif pcntl

# Wyczyść cache APT
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Dodaj Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Skopiuj pliki aplikacji
COPY --chown=www-data:www-data . /var/www

# Ustaw uprawnienia (ważne dla Laravel)
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache || true

# Zainstaluj zależności PHP
RUN composer install --no-dev --optimize-autoloader

# Wygeneruj klucz aplikacji (jeśli nie istnieje)
RUN php artisan config:clear

# Ustaw port, na którym Laravel ma nasłuchiwać
ENV PORT=80

# Laravel uruchamiany przez wbudowany serwer PHP
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=80"]
