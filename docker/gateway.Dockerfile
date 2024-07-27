FROM nginx:1.27.0-bookworm

WORKDIR /var/www

RUN apt-get update && apt-get install -y build-essential

COPY ./docker/deps/openssl-1.0.2q.tar.gz .

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

# cleanup
RUN rm openssl-1.0.2q.tar.gz && rm -rf openssl-1.0.2q

COPY ./docker/vars/gateway/arm-linux-gnueabihf.conf /etc/ld.so.conf.d/arm-linux-gnueabihf.conf

RUN ldconfig

COPY --chown=0:0 ./docker/vars/gateway/etc /etc/dnas 
COPY --chown=0:0 ./docker/vars/gateway/sites-enabled /etc/nginx/sites-enabled
COPY --chown=0:0 ./docker/vars/gateway/nginx.conf /etc/nginx/nginx.conf

COPY --chown=www-data:www-data ./docker/vars/gateway/var/www /var/www

CMD ["nginx", "-g", "daemon off;"]
