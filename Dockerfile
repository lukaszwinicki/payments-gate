# Use the official PHP image as a base image
FROM php:8.2-fpm

# Set working directory
WORKDIR /var/www

# Install system dependencies
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

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql pdo_pgsql mbstring zip exif pcntl

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy the existing application directory permissions to the working directory
COPY --chown=www-data:www-data . /var/www

# Set appropriate permissions to start.sh before switching user
COPY ./docker-compose/start.sh /start.sh
RUN chmod +x /start.sh

# Changing the owner of folders that require writing
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Expose port 80 for HTTP
EXPOSE 80

# Change current user to www
USER www-data

# Run the start script
CMD ["/start.sh"]
