FROM php:8.2-fpm

RUN apt update && \
    apt upgrade -y && \
    apt install -y curl nano zip wget bash

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions gd pdo_mysql xdebug apcu intl zip bcmath curl opcache xml memcache memcached redis

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
