SHELL = /bin/sh

UID := $(shell id -u)
GID := $(shell id -g)

COMPOSE_FILES := $(shell ls -x docker-compose.*.yaml)
COMPOSE_FILES_PARAM := -f $(shell echo ${COMPOSE_FILES} | sed -r 's/ / \-f /g')

DOCKER_BIN := $(shell which docker)

init:
	cp .env.example .env
	sed -i "s/GID=1000/GID=${GID}/" .env
	sed -i "s/UID=1000/UID=${UID}/" .env
	${DOCKER_BIN} compose -f docker-compose.infra.yaml pull biomysql

build:
	${DOCKER_BIN} compose ${COMPOSE_FILES_PARAM} build 

composer-install:
	${DOCKER_BIN} compose -f docker-compose.infra.yaml -f docker-compose.override.yaml run biofpm composer install

test:
	${DOCKER_BIN} compose -f docker-compose.infra.yaml -f docker-compose.override.yaml run --rm biofpm composer test

stan:
	${DOCKER_BIN} compose -f docker-compose.infra.yaml -f docker-compose.override.yaml run --rm biofpm composer stan

run: disable-systemd-resolved
	${DOCKER_BIN} compose ${COMPOSE_FILES_PARAM} up

run-daemon: disable-systemd-resolved
	${DOCKER_BIN} compose ${COMPOSE_FILES_PARAM} up -d

down:
	${DOCKER_BIN} compose ${COMPOSE_FILES_PARAM} down
	make enable-systemd-resovled

disable-systemd-resolved:
	sudo mv /etc/resolv.conf /etc/.resolv.conf
	sudo systemctl disable systemd-resolved
	sudo systemctl stop systemd-resolved

enable-systemd-resovled:
	sudo mv /etc/.resolv.conf /etc/resolv.conf
	sudo systemctl enable systemd-resolved
	sudo systemctl start systemd-resolved