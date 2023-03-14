FROM php:8.1.2-fpm




RUN apt-get update && apt-get install -y \
openssl nginx bash git nano libicu-dev libzip-dev cron libxml2-dev libpng-dev libxslt-dev libonig-dev \
&& curl -sS https://getcomposer.org/installer | php -- \
&& mv composer.phar /usr/local/bin/composer 

RUN apt-get install -y sudo
#php-fpm permissions are a pain for symfony
#Update www-data user to give him all permissions
RUN sed -i 's/www-data:x:82:82/www-data:x:1000:1000/g' /etc/passwd
RUN sed -i 's/www-data:x:82:/www-data:x:1000:/g' /etc/group

#Give wwww-data user sudo permissions
RUN echo "www-data ALL=(ALL) NOPASSWD: ALL" >> /etc/sudoers

RUN curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.deb.sh' | sudo -E bash \
&& sudo apt install symfony-cli


RUN docker-php-ext-configure intl
RUN docker-php-ext-install pdo pdo_mysql opcache intl zip calendar dom mbstring gd xsl
RUN pecl install apcu && docker-php-ext-enable apcu

#enable JPEG support for GD.
RUN apt-get install -y libjpeg-dev 
RUN docker-php-ext-configure gd --with-jpeg 
RUN docker-php-ext-install -j$(nproc) gd

#Install node and npm
RUN curl -sL https://deb.nodesource.com/setup_16.x | bash -
RUN apt-get install -y nodejs
#
#Install ssh
RUN apt-get install -y ssh-client

#FTP
RUN apt-get install -y vsftpd
RUN update-rc.d vsftpd defaults

#mkdir /var/www/html/public if it does not exist
RUN mkdir -p /var/www/html/public


# www-data user/group did not have access to website's directory and files. Give them access
RUN usermod -u 1000 www-data
RUN groupmod -g 1000 www-data
RUN chown -R www-data:www-data /var/www/html

WORKDIR /var/www/html

# cmd nginx and php-fpm
CMD service nginx start && php-fpm