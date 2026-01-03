FROM strm/dnsmasq

RUN apk add --no-cache gettext

COPY docker/vars/dns/dnsmasq.conf /etc/dnsmasq.template.conf
COPY --chmod=754 docker/vars/dns/entrypoint.sh /var/entrypoint.sh

ENTRYPOINT [ "/var/entrypoint.sh" ]