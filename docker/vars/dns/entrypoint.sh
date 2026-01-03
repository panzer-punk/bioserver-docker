#!/bin/sh
set -eu

envsubst '${SERVER_IP} ${ROUTER_IP}' < /etc/dnsmasq.template.conf > /etc/dnsmasq.conf

dnsmasq -k