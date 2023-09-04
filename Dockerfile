ARG PHP_IMAGE=php:8.2-cli

FROM ${PHP_IMAGE} as php

# Maj list paquet
RUN apt-get update \
    && apt-get install -y wget \
    # Xdebug
    && pecl install xdebug-3.2.2 \
    && docker-php-ext-enable xdebug \
    # zip
    && apt-get install -y zlib1g-dev libzip-dev \
    && docker-php-ext-install zip

# Composer
COPY --from=composer:2.2 /usr/bin/composer /usr/local/bin/composer

# clean
RUN rm -rf /var/lib/apt/lists/* \
    && apt-get clean

CMD tail -f /dev/null
