FROM nimmis/apache-php7

RUN apt-get update && apt-get install -yq git php7.0-mbstring php-xml

RUN rm -rf /var/www/html
ADD app /var/www/html
WORKDIR /var/www/html

RUN composer install
RUN chown -R www-data:www-data /var/www/html
RUN sed -i '166s/None/All/' /etc/apache2/apache2.conf
