FROM php:8.2-apache

# Enable Apache mod_rewrite (required for .htaccess routing)
RUN a2enmod rewrite

# Install system dependencies for GD (used by Intervention Image v3)
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg62-turbo-dev \
    libwebp-dev \
    libfreetype6-dev \
    unzip \
    git \
    && rm -rf /var/lib/apt/lists/*

# Configure and install PHP extensions
RUN docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
        --with-webp \
    && docker-php-ext-install -j$(nproc) gd mysqli

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy Apache virtual host config
COPY deployment/000-default.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www

# Copy application source
COPY . .

# Install PHP dependencies (production, no dev tools)
RUN composer install --no-dev --optimize-autoloader

# Ensure upload directory exists with correct ownership
RUN mkdir -p public/image \
    && chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www \
    && chmod -R 775 public/image

EXPOSE 80
