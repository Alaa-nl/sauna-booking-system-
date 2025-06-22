FROM php:8.1-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    curl \
    libcurl4-openssl-dev \
    libxml2-dev \
    libicu-dev \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql zip opcache intl xml curl

# Configure and install GD extension
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy custom PHP configuration
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
RUN echo "memory_limit = 256M" >> "$PHP_INI_DIR/php.ini"
RUN echo "upload_max_filesize = 20M" >> "$PHP_INI_DIR/php.ini"
RUN echo "post_max_size = 20M" >> "$PHP_INI_DIR/php.ini"
RUN echo "max_execution_time = 600" >> "$PHP_INI_DIR/php.ini"
RUN echo "date.timezone = UTC" >> "$PHP_INI_DIR/php.ini"

# Set working directory
WORKDIR /app

# Create cache and log directories and set permissions
RUN mkdir -p /app/logs \
    && chown -R www-data:www-data /app \
    && chmod -R 775 /app

# Set user to root temporarily for better install permissions
USER root

# Copy application files
COPY --chown=www-data:www-data ./app /app

# Install dependencies
RUN cd /app && composer install --no-dev --optimize-autoloader --no-interaction

# Copy custom PHP-FPM configuration
COPY php-fpm.conf /usr/local/etc/php-fpm.d/www.conf

# Set proper permissions and switch back to www-data
RUN chown -R www-data:www-data /app \
    && find /app -type d -exec chmod 755 {} \; \
    && find /app -type f -exec chmod 644 {} \; \
    && chmod -R 775 /app/logs

# Create logs directory
RUN mkdir -p /var/log/php \
    && chown -R www-data:www-data /var/log/php

# Set non-root user for better security
USER www-data

# Expose PHP-FPM port
EXPOSE 9000