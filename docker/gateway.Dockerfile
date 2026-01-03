FROM debian:bullseye

WORKDIR /var/www

COPY ./docker/deps/* .
COPY ./docker/vars/gateway/arm-linux-gnueabihf.conf /etc/ld.so.conf.d/arm-linux-gnueabihf.conf

RUN apt-get update && apt-get install -y --no-install-recommends \
        build-essential \
        wget \
        libpcre3 \
        libpcre3-dev \
        libexpat1 \
        libexpat1-dev \
        libxml2 \
        libxml2-dev \
        libxslt1-dev \
        libxslt1.1 \
    #Setting up openssl
    && tar xzvf openssl-1.0.2q.tar.gz \
    && cd openssl-1.0.2q \
    && ./config --prefix=/opt/openssl-1.0.2 \
        --openssldir=/etc/ssl \
        shared enable-weak-ssl-ciphers \
        enable-ssl3 enable-ssl3-method \
        enable-ssl2 \
        -Wl,-rpath=/opt/openssl-1.0.2/lib \
    && make \
    && make install \
    && cd ../ \
    && rm openssl-1.0.2q.tar.gz && rm -rf openssl-1.0.2q \
    #Compiling Apache2
    && ldconfig \
    && tar xzvf httpd-2.4.61.tar.gz \
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
    && cd /var/www \
    && rm httpd-2.4.61.tar.gz && rm -rf httpd-2.4.61 \
    && rm apr-1.6.5.tar.gz && rm -rf apr-1.6.5 \
    && rm apr-util-1.6.3.tar.gz && rm -rf apr-util-1.6.3 \
    #Cleanup 
    && apt-get autoremove \
    && rm -rf /var/lib/apt/lists/*

COPY --chown=0:0 ./docker/vars/gateway/etc /etc/dnas 
COPY --chown=www-data:www-data ./web/public /var/www/public
COPY --chown=www-data:www-data ./docker/vars/gateway/static /var/www/public

WORKDIR /var/www

COPY ./docker/vars/gateway/httpd.conf /opt/gateway/conf/httpd.conf
COPY --chmod=754 ./docker/vars/gateway/start.sh /var/www/

CMD [ "sh", "-c", "/var/www/start.sh" ]