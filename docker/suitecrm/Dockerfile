FROM php:8.1-fpm-bookworm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    curl \
    git \
    unzip \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libicu-dev \
    libkrb5-dev \
    libldap2-dev \
    libsodium-dev \
    libxml2-dev \
    libonig-dev \
    default-mysql-client \
    && rm -rf /var/lib/apt/lists/*

# Configure and install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        mysqli \
        pdo_mysql \
        zip \
        gd \
        intl \
        ldap \
        sodium \
        opcache \
        mbstring \
        xml \
        bcmath

# Set working directory
WORKDIR /var/www/html

EXPOSE 9000
