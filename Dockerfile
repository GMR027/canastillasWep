# --- Assets (SASS, JS e imagenes) ---
FROM node:20-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY gulpfile.js ./
COPY src ./src
RUN npx gulp build

# --- Imagen final ---
FROM php:8.2-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libcurl4-openssl-dev libonig-dev \
    && docker-php-ext-install mysqli curl mbstring \
    && rm -rf /var/lib/apt/lists/* \
    && a2enmod rewrite

COPY deployment/000-default.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www

COPY . .
COPY --from=assets /app/build ./build

RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www

EXPOSE 80
