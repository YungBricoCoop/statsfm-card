FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
	libfreetype6-dev \
	libjpeg62-turbo-dev \
	libpng-dev \
	zip \
	unzip \
	git \
	&& docker-php-ext-install -j$(nproc) gd fileinfo

COPY index.php /var/www/html/index.php
COPY clean.php /var/www/html/clean.php
COPY constants.php /var/www/html/constants.php
COPY composer.json /var/www/html/composer.json
RUN mkdir -p /var/www/html/cache

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

RUN composer install

EXPOSE 2812
CMD php -S 0.0.0.0:2812 -t /var/www/html
