FROM php:8.2-apache

WORKDIR /var/www/html

# System packages + PHP extensions needed for LifeTechOCMS
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql zip gd \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

# Apache virtual host config
COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf

# Optional custom php settings
RUN printf "upload_max_filesize=64M\npost_max_size=64M\nmemory_limit=512M\nmax_execution_time=240\ndate.timezone=Africa/Lagos\n" > /usr/local/etc/php/conf.d/lifetech.ini

# Copy project files
COPY . /var/www/html/

# Permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 80