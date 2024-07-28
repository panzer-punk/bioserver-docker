FROM nginx:1.27.0-bookworm

WORKDIR /var/www

RUN apt-get update && apt-get install -y build-essential

COPY --chown=0:0 ./docker/vars/gateway/etc /etc/dnas 
COPY --chown=0:0 ./docker/vars/gateway/sites-enabled /etc/nginx/sites-enabled
COPY --chown=0:0 ./docker/vars/gateway/nginx.conf /etc/nginx/nginx.conf

COPY --chown=www-data:www-data ./docker/vars/gateway/var/www /var/www

CMD ["nginx", "-g", "daemon off;"]
