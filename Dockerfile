FROM nginx:latest

# Create app directory
RUN mkdir -p /usr/src/app
WORKDIR /usr/src/app

# Install app dependencies
# COPY composer.json /usr/src/app/
# RUN composer install

# Bundle app source
COPY . /usr/src/app

RUN chown www-data:www-data /usr/src/app

EXPOSE 4941
ENTRYPOINT nginx -g 'daemon off;'
