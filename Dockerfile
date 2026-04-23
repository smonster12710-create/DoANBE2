FROM php:8.4-fpm

# Cài đặt các system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# Xóa cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Cài đặt PHP extensions cần thiết cho Laravel
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Lấy Composer bản mới nhất từ image chính thức
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Thiết lập thư mục làm việc
WORKDIR /var/www

USER www-data