FROM openjdk:17.0.2-jdk-slim-bullseye

ARG SERVER_PATH
ARG RUN_FILE_PATH

COPY ./docker/deps/mysql-connector-j_8.0.32-1debian11_all.deb /tmp
COPY --chown=www-data:www-data --chmod=754 $RUN_FILE_PATH /var/www/run.sh
COPY --chown=www-data:www-data $SERVER_PATH /var/www/bioserver

WORKDIR /var/www

RUN dpkg --install /tmp/mysql-connector-j_8.0.32-1debian11_all.deb \
    && javac -cp /usr/share/java/mysql-connector-j-8.0.32.jar:. ./bioserver/*.java \
    && mkdir -p bin/bioserver \
    && mv bioserver/*.class bin/bioserver \
    && mkdir lib \
    && cp /usr/share/java/mysql-connector-j-8.0.32.jar lib/mysql-connector.jar \
    && rm /tmp/mysql-connector-j_8.0.32-1debian11_all.deb

ENTRYPOINT [ "sh", "-c", "/var/www/run.sh" ]



