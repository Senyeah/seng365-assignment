FROM nimmis/apache-php7

RUN apt-get update && apt-get install -yq git php-pear php7.0-dev libcurl4-openssl-dev pkg-config libssl-dev libsslcommon2-dev

# Copy source code

ADD app /var/www/html
WORKDIR /var/www/html

# Fix Permissions
# Enable mod_rewrite and permit .htaccess files
# Install MongoDB PHP Extension and install its library

RUN chown -R www-data:www-data /var/www/html && \
    a2enmod rewrite && \
    sed -i '166s/None/All/' /etc/apache2/apache2.conf && \
    pecl install mongodb 1>/dev/null || true && \
    sed -i '850s/.*/extension=mongodb.so/' /etc/php/7.0/apache2/php.ini && \
    composer install --ignore-platform-reqs

CMD apachectl -DFOREGROUND
