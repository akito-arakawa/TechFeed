FROM php:8.2-cli

RUN apt-get update && \
    apt-get install -y cron procps && \
    docker-php-ext-install pdo pdo_mysql && \
    touch /var/log/cron.log && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /app