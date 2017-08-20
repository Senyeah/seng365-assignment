FROM nimmis/apache-php7

RUN apt-get update && apt-get install -yq git php-pear php7.0-dev libcurl4-openssl-dev pkg-config libssl-dev libsslcommon2-dev

# Copy source code

RUN rm -rf /var/www/html
ADD app /var/www/html
WORKDIR /var/www/html

# Fix Permissions

RUN chown -R www-data:www-data /var/www/html

# Enable mod_rewrite and permit .htaccess files

RUN a2enmod rewrite
RUN sed -i '166s/None/All/' /etc/apache2/apache2.conf

# Install MongoDB PHP Extension and install its library

RUN pecl install mongodb || true
RUN sed -i '850s/.*/extension=mongodb.so/' /etc/php/7.0/apache2/php.ini
RUN composer install --ignore-platform-reqs
