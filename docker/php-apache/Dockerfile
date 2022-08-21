FROM php:8.1-apache

RUN docker-php-ext-install pdo_mysql \
    && apt-get update \
    && apt-get install -y --no-install-recommends openssl libssl-dev libcurl4-openssl-dev \
    && pecl install mongodb \
    && echo "extension=mongodb.so" >> /usr/local/etc/php/php.ini \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# CMD ["php-apache"]
#    && cp /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini \
# EXPOSE 9000