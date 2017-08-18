FROM nginx:latest

# Create app directory
RUN mkdir -p /usr/src/app
WORKDIR /usr/src/app

# Install app dependencies
# COPY composer.json /usr/src/app/
# RUN composer install

# Bundle app source
COPY . /usr/src/app

RUN cat nginx.conf > /etc/nginx/conf.d/default.conf

EXPOSE 4941
CMD ["nginx", "-g", "'daemon off;'"]
