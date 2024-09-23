FROM php:8.3-fpm

RUN docker-php-ext-install pdo pdo_mysql mysqli
RUN docker-php-ext-enable mysqli

#Installing and setting up DNAS
WORKDIR /tmp

# COPY --from=composer:2.7.8 /usr/bin/composer /usr/local/bin/composer
# COPY --chown=www-data:www-data ./web /var/www
COPY ./docker/vars/web/yy-log.conf /usr/local/etc/php-fpm.d/00-log.conf
COPY ./docker/vars/web/openssl.cnf /usr/lib/ssl/openssl.cnf

USER www-data

WORKDIR /var/www

# RUN composer install --no-dev

EXPOSE 9000

CMD [ "php-fpm" ]