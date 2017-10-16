FROM node:latest

RUN mkdir -p /home/node/app

COPY . /home/node/app

RUN cd /home/node/app && \
    rm -r node_modules && \
    rm -r webpack_cache && \
    npm i

EXPOSE 4563
