FROM debian:bullseye

ARG DNAS_SRC
ARG DNAS_DEST
ARG FPM_HOST

WORKDIR /var/www

RUN apt-get update && apt-get install -y build-essential wget

COPY ./docker/deps/openssl-1.0.2q.tar.gz .

#Setting up weak ciphers
RUN tar xzvf openssl-1.0.2q.tar.gz \
    && cd openssl-1.0.2q \
    && ./config --prefix=/opt/openssl-1.0.2 \
        --openssldir=/etc/ssl \
        shared enable-weak-ssl-ciphers \
        enable-ssl3 enable-ssl3-method \
        enable-ssl2 \
        -Wl,-rpath=/opt/openssl-1.0.2/lib \
    && make \
    && make install

RUN rm openssl-1.0.2q.tar.gz && rm -rf openssl-1.0.2q

COPY ./docker/vars/gateway/arm-linux-gnueabihf.conf /etc/ld.so.conf.d/arm-linux-gnueabihf.conf

RUN ldconfig

#Compiling Apache2
RUN apt-get install -y libpcre3 \
    libpcre3-dev \
    libexpat1 \
    libexpat1-dev \
    libxml2 \
    libxml2-dev \
    libxslt1-dev \
    libxslt1.1

COPY ./docker/deps/httpd-2.4.61.tar.gz .
COPY ./docker/deps/apr-1.6.5.tar.gz .
COPY ./docker/deps/apr-util-1.6.3.tar.gz .

RUN tar xzvf httpd-2.4.61.tar.gz \
    && cd httpd-2.4.61/srclib/ \
    && tar xzvf ../../apr-1.6.5.tar.gz \
    && tar xzvf ../../apr-util-1.6.3.tar.gz \
    && ln -s apr-1.6.5 apr \
    && ln -s apr-util-1.6.3 apr-util \
    && cd ../ \
    && ./configure --prefix=/opt/gateway \
        --with-included-apr \
        --with-ssl=/opt/openssl-1.0.2 \
        --enable-ssl \
    && make \
    && make install \
    && apt-get install -y php libapache2-mod-php7.4 

RUN rm httpd-2.4.61.tar.gz && rm -rf httpd-2.4.61 \
    && rm apr-1.6.5.tar.gz && rm -rf apr-1.6.5 \
    && rm apr-util-1.6.3.tar.gz && rm -rf apr-util-1.6.3

COPY --chown=0:0 ./docker/vars/gateway/etc /etc/dnas 
COPY --chown=www-data:www-data ./web/public /var/www/public
COPY --chown=www-data:www-data ./docker/vars/gateway/static /var/www/public

RUN apt-get install -y iputils-ping

#Start this shit
WORKDIR /var/www
#better pass to conf.d
COPY ./docker/vars/gateway/httpd.conf /opt/gateway/conf/httpd.conf
COPY --chmod=754 ./docker/vars/gateway/start.sh /var/www/

CMD [ "sh", "-c", "/var/www/start.sh" ]