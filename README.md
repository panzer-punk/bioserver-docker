# Open Source Outbreak Server

Hi all

To briefly state the goal behind this project, it's simply to preserve [dev ghostline](https://gitlab.com/users/gh0stl1ne/projects)'s Bioserver1 & 2 projects, as neither of these are available on github and both require a bit of work to get up and running.

## Setup
**DO IT BEFORE RUNNING GAME SERVER**
1. run `make init`
2. set your server IP in .env (SERVER_IP=)
3. set your router IP or secondary dns (ROUTER_IP=)
4. build containers `make build`

### Running game server
**For local server set FORCE_DEV_LOGIN=true in .env**

1. simple run `make run-daemon` or `make run`

### After shutdown
**DO NOT FORGET TO ENABLE systemd-resolved**\
Simply run `make enable-systemd-resovled`

## Develop

### Web

By default, the web application is built in production mode and changes in the code will not be applied until the application is rebuilt.<br>
To make changes to the code without rebuilding, follow these steps:
1. Set APP_PRODUCTION_BUILD variable to false in .env (APP_PRODUCTION_BUILD=false)
2. Override biofpm volume by `cp docker/docker-compose.override.dev.yaml docker-compose.override.yaml`
3. Install composer dependecies `make composer-install`

#### Testing

**IMPORTANT:** Before running tests, make sure to start the biomysql container, otherwise you may encounter database connection errors on first run.<br>
You can start it with: `docker compose -f docker-compose.infra.yaml up -d biomysql`

For running tests, there are two commands available:
- `make test` - runs PHPUnit tests
- `make stan` - runs PHPStan static analysis