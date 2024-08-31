FROM php:8.3-fpm

RUN docker-php-ext-install pdo pdo_mysql mysqli
RUN docker-php-ext-enable mysqli

#Installing and setting up DNAS
WORKDIR /tmp

# COPY --from=composer:2.7.8 /usr/bin/composer /usr/local/bin/composer
# COPY --chown=www-data:www-data ./web /var/www

USER www-data

WORKDIR /var/www

# RUN composer install --no-dev

EXPOSE 9000

CMD [ "php-fpm" ]