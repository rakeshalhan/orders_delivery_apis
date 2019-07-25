FROM php:7.2-fpm-alpine

RUN docker-php-ext-install -j$(nproc) iconv mysqli pdo pdo_mysql

RUN curl -sS https://getcomposer.org/installer | \
    php -- --install-dir=/usr/bin/ --filename=composer