FROM nginx:latest

# Install app dependencies
# COPY composer.json /usr/src/app/
# RUN composer install

# Delete old and copy configuration new file
RUN rm /etc/nginx/conf.d/*.conf
COPY ./containers/nginx/nginx.conf /etc/nginx/conf.d/

RUN mkdir -p /usr/src/app
WORKDIR /usr/src/app

COPY app /usr/src/app

EXPOSE 4941
ENTRYPOINT nginx -g 'daemon off;'
