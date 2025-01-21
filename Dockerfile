ARG NODE_VERSION=20-alpine

# Use an official Node.js image as the base image
FROM node:${NODE_VERSION} as base

ENV NODE_ENV=production
WORKDIR /app


FROM base as build

COPY package*.json ./
RUN npm i
RUN npm install --omit=dev
COPY . .
RUN npm run build
RUN npm prune


FROM php:8.2-apache as web

# Install Additional System Dependencies
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    libsodium23 libsodium-dev

RUN apt list --installed|grep libsodium

# Add php extension
RUN docker-php-ext-install pdo_mysql zip sodium ftp pcntl
RUN pecl install redis && docker-php-ext-enable redis
# Set homedir

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf


# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Enable Apache mod_rewrite for URL rewriting
RUN a2enmod rewrite

COPY . /var/www/html
WORKDIR /var/www/html

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install project dependencies
RUN composer install

# Copy js build
COPY --from=build /app/public/build/ /var/www/html/public/build/


# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
