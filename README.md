# VaccinateOH Informational Site

This repo contains the basic pieces to build and run the VaccinateOH WordPress site.

## Importing a Dev Environment

```
cd build
make setup-dev-environment
```

# Staging Deploy

Put the following in `build/env.mk`:

```
COMPOSE_FILE=staging.docker-compose.yml
DB_PASSWORD=randomstringplease
```

Then run `make setup-environment` to launch the environment.  You will most
likely need to import a DB and do a search replace as well.


# New Site Install

Create an `env.mk` to override any default variables. The only necessary
variable to be overridden is the `LOCAL_DOMAIN` variable.

# Legacy Sites

Sites that do not use a `src` directory for application code require a small
configuration change.  Place the following in `app.mk`:

```
APP_DIR=
```

This tells the configuration that the application code lives in the same
directory as the PROJECT_ROOT (which is the parent directory of build by
default).

