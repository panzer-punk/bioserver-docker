FROM debian:12.6-slim


RUN apt-get update && apt-get install -y \ 
    build-essential \
    tar \
    binutils \
    gcc \
    libpcre3-dev \
    libssl-dev \
    zlib1g-dev \
    ca-certificates \
    gnupg2 \
    wget \
    libgd-dev \
    libkrb5-dev \
    git 

WORKDIR /tmp

#Setting up openssl

RUN wget https://github.com/openssl/openssl/releases/download/openssl-3.3.1/openssl-3.3.1.tar.gz \
    && tar xzvf openssl-3.3.1.tar.gz 

#Setting up nginx
RUN wget https://nginx.org/download/nginx-1.26.1.tar.gz && \
    tar xzvf nginx-1.26.1.tar.gz && \
    git clone https://github.com/stnoonan/spnego-http-auth-nginx-module.git nginx-1.26.1/spnego-http-auth-nginx-module

# RUN apt-get remove -y nginx nginx-common nginx-core nginx-full

WORKDIR /tmp/nginx-1.26.1
#Compile nginx
RUN ./configure \
        --user=www-data \
        --with-debug \
        --group=www-data \
        --prefix=/usr/share/nginx \
        --sbin-path=/usr/sbin/nginx \
        --conf-path=/etc/nginx/nginx.conf \
        --pid-path=/run/nginx.pid \
        --lock-path=/run/lock/subsys/nginx \
        --error-log-path=/var/log/nginx/error.log \
        --http-log-path=/var/log/nginx/access.log \
        --with-http_gzip_static_module \
        --with-http_stub_status_module \
        --with-http_ssl_module \
        --with-pcre \
        --with-http_image_filter_module \
        --with-file-aio \
        --with-http_dav_module \
        --with-http_flv_module \
        --with-http_mp4_module \
        --with-http_gunzip_module \
        --add-module=spnego-http-auth-nginx-module \
        --with-openssl=/tmp/openssl-3.3.1 \
        --with-openssl-opt="shared enable-weak-ssl-ciphers enable-ssl3 enable-ssl3-method enable-ssl2 -Wl,-rpath=/opt/openssl-3.3.1/lib" \
        --with-ld-opt="-L/opt/openssl-3.3.1/lib" \
    && make \
    && make install

#cleanup 
RUN rm -rf /tmp/*

# COPY --chown=www-data:www-data ./bioserv1/www /var/www/dnas/00000002
# COPY --chown=www-data:www-data ./bioserv2/www /var/www/dnas/00000010

COPY --chown=www-data:www-data ./web/public /var/www/public
COPY --chown=www-data:www-data ./docker/vars/gateway/static /var/www/static

COPY --chown=0:0 ./docker/vars/gateway/etc /etc/dnas 
RUN cat /etc/dnas/ca-cert.pem >> /etc/dnas/cert-jp.pem \
    && cat /etc/dnas/ca-cert.pem >> /etc/dnas/cert-us.pem \
    && cat /etc/dnas/ca-cert.pem >> /etc/dnas/cert-eu.pem

COPY --chown=0:0 ./docker/vars/gateway/sites-enabled /etc/nginx/sites-enabled
COPY --chown=0:0 ./docker/vars/gateway/nginx.conf /etc/nginx/nginx.conf

CMD ["nginx", "-g", "daemon off;"]
