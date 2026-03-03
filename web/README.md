# Web Module

This directory contains the web interface code for the game server.
It handles login and session management flows on the game server side, and also includes part of the DNAS logic used by the project.

## Supported ENV variables

- `APP_PRODUCTION_BUILD` - Enables production mode behavior.
- `FORCE_DEV_LOGIN` - Enables development login flow.
- `LOG_ERROR` - Enables error logging.
- `LOG_ERROR_DETAILS` - Enables detailed error logging.
- `LOG_LEVEL` - Sets logger level (Monolog).
- `DB_HOST` - Database host.
- `DB_DATABASE` - Database name.
- `DB_USER` - Database user.
- `DB_PASSWORD` - Database password.
