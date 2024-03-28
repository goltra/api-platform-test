FROM php:8.2-apache
WORKDIR /var/www/html
#COPY . .
RUN a2enmod rewrite
RUN apt-get update
RUN apt-get install -y libxml2-dev vim
RUN docker-php-ext-install pdo pdo_mysql

#Soporte para GD
RUN apt-get install -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install zip

#Xdebug
RUN pecl install xdebug-3.2.0 && docker-php-ext-enable xdebug
RUN echo 'xdebug.client_host=host.docker.internal\n\
xdebug.start_with_request=yes\n\
xdebug.mode=debug' >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini;