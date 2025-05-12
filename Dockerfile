FROM php:8.0-fpm

# Install system dependencies
RUN sed -i 's/deb.debian.org/mirrors.ustc.edu.cn/g' /etc/apt/sources.list \
    && apt-get update && apt-get install -y \
    libzip-dev

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql zip

EXPOSE 9000