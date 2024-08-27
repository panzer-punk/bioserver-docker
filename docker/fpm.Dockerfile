FROM php:8.3-fpm

ARG DB_USER
ARG DB_PASSWORD
ARG BIO1_DB_DATABASE
ARG BIO2_DB_DATABASE

RUN docker-php-ext-install pdo pdo_mysql mbstring mysqli
RUN docker-php-ext-enable mysqli

#Installing and setting up DNAS
WORKDIR /tmp

COPY --chown=www-data:www-data ./docker/vars/php/www /var/www

COPY --chown=www-data:www-data ./bioserv1/www /var/www/dnas/00000002
RUN sed -i "s\{{DB_USER}}\\${DB_USER}\g" /var/www/dnas/00000002/config.ini \
    && sed -i "s\{{DB_PASSWORD}}\\${DB_PASSWORD}\g" /var/www/dnas/00000002/config.ini \
    && sed -i "s\{{DB_DATABASE}}\\${BIO1_DB_DATABASE}\g" /var/www/dnas/00000002/config.ini

COPY --chown=www-data:www-data ./bioserv2/www /var/www/dnas/00000010
RUN sed -i "s\{{DB_USER}}\\${DB_USER}\g" /var/www/dnas/00000010/config.ini \
    && sed -i "s\{{DB_PASSWORD}}\\${DB_PASSWORD}\g" /var/www/dnas/00000010/config.ini \
    && sed -i "s\{{DB_DATABASE}}\\${BIO2_DB_DATABASE}\g" /var/www/dnas/00000010/config.ini


USER www-data

WORKDIR /var/www

EXPOSE 9000

CMD [ "php-fpm" ]