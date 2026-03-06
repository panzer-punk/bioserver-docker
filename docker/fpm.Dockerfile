FROM php:8.3-fpm AS base

RUN docker-php-ext-install pdo pdo_mysql mysqli && \
    docker-php-ext-enable mysqli && \
    apt-get update && apt-get install -y --no-install-recommends unzip && \
    mkdir /home/www-data && \
    chmod 755 /home/www-data && \
    chown -R www-data:www-data /home/www-data && \
    rm -rf /var/lib/apt/lists/*

#Installing and setting up DNAS
WORKDIR /tmp

COPY --from=composer:2.9.3 /usr/bin/composer /usr/local/bin/composer
COPY --chown=www-data:www-data ./web /var/www
COPY ./docker/vars/web/yy-log.conf /usr/local/etc/php-fpm.d/00-log.conf
COPY ./docker/vars/web/openssl.cnf /usr/lib/ssl/openssl.cnf

WORKDIR /var/www

EXPOSE 9000

CMD [ "php-fpm" ]

FROM base AS development

ARG UID
ARG GID

RUN usermod -u $UID -d /home/www-data www-data && \
    groupmod -g $GID www-data

USER www-data

FROM base AS production

USER www-data

RUN composer install --no-interaction --optimize-autoloader --no-dev