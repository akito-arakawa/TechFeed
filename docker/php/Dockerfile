FROM php:8.2-fpm-bullseye
SHELL ["/bin/bash", "-oeux", "pipefail", "-c"]

# xdebug はビルド時にオン/オフ切替
ARG INSTALL_XDEBUG=false
ARG TZ=Asia/Tokyo

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_HOME=/composer \
    PATH=/composer/vendor/bin:$PATH

COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

RUN set -eux; \
    apt-get update && \
    apt-get install -y --no-install-recommends \
      git unzip tzdata libzip-dev libicu-dev zlib1g-dev \
      autoconf make gcc g++ pkg-config \
    && ln -sf /usr/share/zoneinfo/${TZ} /etc/localtime && echo ${TZ} > /etc/timezone \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql intl zip bcmath opcache \
    && if [ "${INSTALL_XDEBUG}" = "true" ]; then \
         pecl install xdebug && docker-php-ext-enable xdebug; \
       fi \
    && apt-get purge -y --auto-remove autoconf make gcc g++ pkg-config \
    && apt-get clean && rm -rf /var/lib/apt/lists/*


WORKDIR /app

COPY ./php.ini /usr/local/etc/php/php.ini

# COPY ./xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini
