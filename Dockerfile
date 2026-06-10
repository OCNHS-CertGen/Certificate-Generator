FROM php:8.2-apache

# Install system dependencies (zip and git are needed for Composer)
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    && rm -rf /var/lib/apt/lists/*

# Install mysqli extension for PHP
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Enable Apache mod_rewrite (optional but recommended for PHP apps)
RUN a2enmod rewrite

# Copy Composer binary
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html/

# Copy composer files and run install before copying rest of files to leverage Docker caching
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-plugins --no-scripts --prefer-dist --optimize-autoloader

# Copy all project files to the container
COPY . ./

# Set permissions for Apache
RUN chown -R www-data:www-data /var/www/html/

# Use the default production configuration
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Expose port 80
EXPOSE 80
