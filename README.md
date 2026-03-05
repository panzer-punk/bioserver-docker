# Open Source Outbreak Server

This project is an all-in-one Docker build for Bioserver1 and Bioserver2, bundling the game server together with DNS services, the built-in DNAS server, and a MySQL database, based on original work by [dev ghostline](https://gitlab.com/users/gh0stl1ne/projects).<br>
It is aimed at fans and fellow enthusiasts, and supports both local server and self-hosted deployments.<br>
The goal is to keep the game alive and accessible for the community by modernizing the stack and making private or local server deployment easy.<br>
Development and testing are focused primarily on local server usage, so self-hosted environments may require custom image adjustments.

## Setup
**DO THIS BEFORE RUNNING THE GAME SERVER**
1. Set your server IP in `.env` (`SERVER_IP=`)
2. Set your router IP or secondary DNS in `.env` (`ROUTER_IP=`)
3. Build containers with `make build`

### Running the game server
**For local server usage, set `FORCE_DEV_LOGIN=true` in `.env`**

1. Simply run `make run-daemon` or `make run`

### After shutdown
**DO NOT FORGET TO ENABLE systemd-resolved**\
Simply run `make enable-systemd-resolved`

## Gateway certificates

The gateway uses a local CA and one SAN server certificate for all DNAS hostnames.

- Certificates are generated inside the gateway container in `/etc/dnas`.
- If certificate files are missing, the gateway generates them automatically on container startup.
- If you need persistent certificates between container recreations, add a host volume for `/etc/dnas`, for example:
  ```yaml
  services:
    biogateway:
      volumes:
        - ./gateway_certs:/etc/dnas
  ```
- Required certificate files in `/etc/dnas`:
  - `ca-cert.pem`
  - `cert.pem`
  - `cert-key.pem`

To reissue certificates manually:
1. Run inside container: `docker compose -f docker-compose.infra.yaml exec biogateway /var/www/reissue-certs.sh`
2. Restart gateway container: `docker compose -f docker-compose.infra.yaml restart biogateway`

## Develop

### Web

By default, the web application is built in production mode and changes in the code will not be applied until the application is rebuilt.<br>
To make changes to the code without rebuilding, follow these steps:
1. Set the `APP_PRODUCTION_BUILD` variable to `false` in `.env` (`APP_PRODUCTION_BUILD=false`)
2. Override biofpm volume by `cp docker/docker-compose.override.dev.yaml docker-compose.override.yaml`
3. Install Composer dependencies with `make composer-install`

#### Testing

**IMPORTANT:** Before running tests, make sure to start the biomysql container, otherwise you may encounter database connection errors on first run.<br>
You can start it with: `docker compose -f docker-compose.infra.yaml up -d biomysql`

To run tests, two commands are available:
- `make test` - runs PHPUnit tests
- `make stan` - runs PHPStan static analysis

## Environment variables by container

### `bio1server` / `bio2server`
- `SERVER_IP`
- `DB_USER`
- `DB_PASSWORD`
- `DB_DATABASE`
- `DB_HOST`
- `JAVA_DB_PARAMS`

### `biodns`
- `SERVER_IP`
- `ROUTER_IP`

### `biomysql`
- `DB_USER`
- `DB_PASSWORD`
- `DB_DATABASE`

### `biofpm` (web)
- `APP_PRODUCTION_BUILD` (build arg + runtime behavior)
- `FORCE_DEV_LOGIN`
- `DB_HOST`
- `DB_DATABASE`
- `DB_USER`
- `DB_PASSWORD`
- `LOG_ERROR` (optional)
- `LOG_ERROR_DETAILS` (optional)
- `LOG_LEVEL` (optional)
- `UID` (build arg used for correct file permissions during web development, defaults to `1000`)
- `GID` (build arg used for correct file permissions during web development, defaults to `1000`)