FROM php:8.1.2-fpm-alpine3.15


RUN apk update \
    && apk --no-cache add $PHPIZE_DEPS openssl-dev nginx bash git nano icu-dev libzip-dev apk-cron libxml2-dev oniguruma-dev libpng-dev libxslt-dev \
    &&  curl -sS https://getcomposer.org/installer | php -- \
    &&  mv composer.phar /usr/local/bin/composer \
    &&  curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.alpine.sh' | bash \
    &&  apk add symfony-cli
RUN docker-php-ext-configure intl
RUN docker-php-ext-install pdo pdo_mysql opcache intl zip calendar dom mbstring gd xsl
RUN pecl install apcu && docker-php-ext-enable apcu


# enable JPEG support for GD.
RUN apk add --no-cache libjpeg-turbo-dev && \
    docker-php-ext-configure gd --with-jpeg && \
    docker-php-ext-install -j$(nproc) gd


# Install python3
RUN apk add --no-cache python3

# Install pip
RUN python3 -m ensurepip
RUN pip3 install --upgrade pip setuptools

# install mysql-connector-python     
RUN pip install mysql-connector-python

RUN pip install requests

# php-fpm and alpine permissions are a pain for symfony 
# Update www-data user to give him all permissions
RUN sed -i 's/www-data:x:82:82/www-data:x:1000:1000/g' /etc/passwd
RUN sed -i 's/www-data:x:82:/www-data:x:1000:/g' /etc/group


WORKDIR /var/www/html

CMD nginx && php-fpm