SHELL = /bin/sh

UID := $(shell id -u)
GID := $(shell id -g)

COMPOSE_FILES := $(filter-out docker-compose.infra.yaml docker-compose.override%.yaml,$(wildcard docker-compose.*.yaml))
COMPOSE_OVERRIDE_FILES := $(wildcard docker-compose.override*.yaml)
COMPOSE_FILES_PARAM := -f docker-compose.infra.yaml $(foreach file,$(COMPOSE_FILES),-f $(file)) $(foreach file,$(COMPOSE_OVERRIDE_FILES),-f $(file))

DOCKER_BIN := $(shell which docker)

init:
	cp .env.example .env
	sed -i "s/GID=1000/GID=${GID}/" .env
	sed -i "s/UID=1000/UID=${UID}/" .env
	${DOCKER_BIN} compose -f docker-compose.infra.yaml pull biomysql

build:
	${DOCKER_BIN} compose ${COMPOSE_FILES_PARAM} build

composer-install:
	${DOCKER_BIN} compose ${COMPOSE_FILES_PARAM} run biofpm composer install

composer-update:
	${DOCKER_BIN} compose ${COMPOSE_FILES_PARAM} run biofpm composer update

composer-bump:
	${DOCKER_BIN} compose ${COMPOSE_FILES_PARAM} run biofpm composer bump

test:
	${DOCKER_BIN} compose ${COMPOSE_FILES_PARAM} run --rm biofpm composer test

stan:
	${DOCKER_BIN} compose ${COMPOSE_FILES_PARAM} run --rm biofpm composer stan

run:
	${DOCKER_BIN} compose ${COMPOSE_FILES_PARAM} up

run-daemon: disable-systemd-resolved
	${DOCKER_BIN} compose ${COMPOSE_FILES_PARAM} up -d

down:
	${DOCKER_BIN} compose ${COMPOSE_FILES_PARAM} down

disable-systemd-resolved:
	sudo mv /etc/resolv.conf /etc/.resolv.conf
	sudo systemctl disable systemd-resolved
	sudo systemctl stop systemd-resolved

enable-systemd-resolved:
	sudo mv /etc/.resolv.conf /etc/resolv.conf
	sudo systemctl enable systemd-resolved
	sudo systemctl start systemd-resolved