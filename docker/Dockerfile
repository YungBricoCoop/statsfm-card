FROM php:8.2-apache

RUN apt-get update \
	&& apt-get install -y --no-install-recommends \
	libfreetype6-dev \
	libjpeg62-turbo-dev \
	libpng-dev \
	zip \
	unzip \
	git \
	cron \
	&& docker-php-ext-configure gd --with-freetype --with-jpeg \
	&& docker-php-ext-install -j$(nproc) gd \
	&& apt-get clean && rm -rf /var/lib/apt/lists/*

RUN mkdir -p /var/www/html/cache /var/www/html/utils
COPY index.php /var/www/html/index.php
COPY clean.php /var/www/html/clean.php
COPY .htaccess /var/www/html/.htaccess
COPY constants.php /var/www/html/constants.php
COPY composer.json /var/www/html/composer.json
COPY docker/apache.conf  /etc/apache2/conf-available/apache.conf
RUN touch /var/log/cron.log

RUN chown -R www-data:www-data /var/www/html/cache && \
	chmod -R 775 /var/www/html/cache

RUN a2enconf apache

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

RUN composer install
 
RUN echo "0 0 * * 0  root /usr/local/bin/php var/www/html/clean.php >> /var/log/cron.log 2>&1" >> /etc/crontab

EXPOSE 80

CMD service cron start && /usr/local/bin/apache2-foreground
