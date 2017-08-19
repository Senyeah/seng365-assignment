FROM nimmis/apache-php7

RUN apt-get update && apt-get install -yq git php7.0-mbstring php-xml

RUN rm -rf /app
ADD app /app
WORKDIR /app

RUN composer install
RUN sed -i '166s/None/All/' /etc/apache2/apache2.conf
