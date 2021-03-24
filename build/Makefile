# VERSION 1.1.4

# Defaults, override in app.mk for a specific project
export PHP_VERSION=7.3

# App Config
export DB_NAME=wordpress
export DB_HOST=db
export DB_USER=$(DB_NAME)
export DB_PASSWORD=wordpress

export ADMIN_USER=buckii
export ADMIN_USER_EMAIL=dev@buckeyeinteractive.com
export TEMPLATE_THEME=toniktheme

# See https://docs.docker.com/compose/reference/envvars/
export COMPOSE_FILE=docker-compose.yml
export COMPOSE_PROJECT_NAME=example
export LOCAL_DOMAIN=$(COMPOSE_PROJECT_NAME).localhost

# WP Basics
export WP_SITEURL=https://$(LOCAL_DOMAIN)
export DOMAIN_CURRENT_SITE=$(LOCAL_DOMAIN)
export NOBLOGREDIRECT=$(WP_SITEURL)

# Multisite Support
export WP_MULTISITE=0
export WP_MULTISITE_SUBDIRS=0
export WP_MULTISITE_SUBDIRS_MAP=

# Container names and exec statements
WEB_CONTAINER_NAME=docker ps --format '{{.Names}}' --filter "name=$(COMPOSE_PROJECT_NAME)_web | head -n1"
DOCKER_WEB_EXEC=docker-compose exec --user=application web
DOCKER_WEB_COMMAND=docker-compose exec -T --user=application web

# Remote Server Parameters
SITE_DOMAIN=example.com
PROD_WEB_SERVER=web1.$(SITE_DOMAIN)
PROD_SSH_USER=ubuntu
PROD_SSH_DEPLOY_USER=deploy
PROD_WP_PATH=/var/www/sites/$(SITE_DOMAIN)/current
PROD_UPLOADS_PATH=/var/www/sites/$(SITE_DOMAIN)/shared/uploads

# Remote Server Parameters (Docker Compose)
# To use these, define PROD_ON_DOCKER_COMPOSE in app.mk
# REPO_NAME below may need to be overridden
REPO_NAME=$(COMPOSE_PROJECT_NAME)
PROD_SSH_DEPLOY_USER_DC=ubuntu
PROD_WP_PATH_DC=/home/ubuntu/$(REPO_NAME)/$(APP_DIR)
PROD_UPLOADS_PATH_DC=$(PROD_WP_PATH_DC)/wp-content/uploads/

# Upload and Setup Dev Env

# Root of the project
export PROJECT_ROOT=..

# Location of app and build config, relative to PROJECT_ROOT
export APP_DIR=src

# Relative to this file
export APP_ROOT=$(PROJECT_ROOT)/$(APP_DIR)

SQL_BACKUP_FILE=backup.sql
export EXPOSE_PORT=3000

# WordPress Updates
ACF_PRO_KEY=b3JkZXJfaWQ9NjM5ODB8dHlwZT1kZXZlbG9wZXJ8ZGF0ZT0yMDE1LTA5LTEwIDIxOjA4OjQx
# Use 0 for false and 1 for true since getenv returns strings
export WP_DEBUG=0
export WP_TABLE_PREFIX=wp_
export SALTS_FILE=$(APP_ROOT)/.salts.php
THEME_NAME=$(COMPOSE_PROJECT_NAME)

# App specific config goes in this file
-include app.mk

# Allow environment specific overrides
-include env.mk

up:
ifndef NO_PULL
	docker-compose pull
endif
	docker-compose up -d

down:
	docker-compose down

logs:
	docker-compose logs -f --tail=100

web-shell:
	$(DOCKER_WEB_EXEC) bash

theme-shell:
	$(DOCKER_WEB_EXEC) sh -c "cd wp-content/themes/$(THEME_NAME) && /bin/bash"

db-shell:
	$(DOCKER_WEB_EXEC) wp db cli

import-db:
	$(DOCKER_WEB_COMMAND) wp db import - < $(SQL_BACKUP_FILE)

export-db:
	$(DOCKER_WEB_COMMAND) wp db export - > $(SQL_BACKUP_FILE)

setup-dev-environment: setup-environment fetch-dev-environment import-db fix-sitename app-build

app-build:
	$(DOCKER_WEB_EXEC) sh -c "cd wp-content/themes/$(THEME_NAME) \
	&& composer install \
	&& yarn"

setup-environment: up $(SALTS_FILE) install-dev-wp-config

$(SALTS_FILE):
	echo "<?php" > $@
	curl https://api.wordpress.org/secret-key/1.1/salt/ >> "$@"

install-dev-wp-config:
	cp wp-config.php.example $(APP_ROOT)/wp-config.php

fetch-dev-environment:
ifdef PROD_ON_DOCKER_COMPOSE
	CNAME=$$(ssh $(PROD_SSH_DEPLOY_USER_DC)@$(PROD_WEB_SERVER) $(WEB_CONTAINER_NAME)) && \
		ssh -C $(PROD_SSH_DEPLOY_USER_DC)@$(PROD_WEB_SERVER) docker exec --user=application $$CNAME wp db export - > $(SQL_BACKUP_FILE)
ifndef NO_FETCH_FILES
	rsync -a --info=progress2 $(PROD_SSH_DEPLOY_USER_DC)@$(PROD_WEB_SERVER):$(PROD_UPLOADS_PATH_DC) $(APP_ROOT)/wp-content/uploads/
endif
else
	ssh -C $(PROD_SSH_DEPLOY_USER)@$(PROD_WEB_SERVER) wp --path=$(PROD_WP_PATH) db export - > $(SQL_BACKUP_FILE)
	ssh $(PROD_SSH_USER)@$(PROD_WEB_SERVER) sudo chown -R $(PROD_SSH_DEPLOY_USER):www-data $(PROD_UPLOADS_PATH)
ifndef NO_FETCH_FILES
	rsync -a --info=progress2 $(PROD_SSH_DEPLOY_USER)@$(PROD_WEB_SERVER):$(PROD_WP_PATH)/wp-content/uploads/ $(APP_ROOT)/wp-content/uploads/
endif
endif

fix-sitename:
	$(DOCKER_WEB_EXEC) wp search-replace '$(SITE_DOMAIN)' '$(LOCAL_DOMAIN)' --all-tables

update-core:
	$(DOCKER_WEB_EXEC) wp core update

search-replace:
	$(DOCKER_WEB_EXEC) wp search-replace $(REPLACE) $(WITH) --all-tables

rename-theme:
	cd $(PROJECT_ROOT) \
		&& cd src/wp-content/themes \
		&& mv $(TEMPLATE_THEME) $(THEME_NAME)

update-plugins:
	$(DOCKER_WEB_EXEC) wp plugin update --all

update-acf-pro:
	$(DOCKER_WEB_EXEC) rm -rf /app/wp-content/plugins/advanced-custom-fields-pro && \
	$(DOCKER_WEB_EXEC) wget -O /app/wp-content/acf-pro.zip "http://connect.advancedcustomfields.com/index.php?p=pro&a=download&k=$(ACF_PRO_KEY)" && \
	$(DOCKER_WEB_EXEC) wp plugin install /app/wp-content/acf-pro.zip --activate && \
	$(DOCKER_WEB_EXEC) rm /app/wp-content/acf-pro.zip

create-user:
	$(DOCKER_WEB_EXEC) wp user create $(NAME) $(EMAIL) --user_pass=password --role=administrator

create-new-site: setup-environment download-core install-new-site rename-theme app-build install-default-plugins activate-new-theme delete-twenty-themes wp-set-defaults

wp-set-defaults:
	$(DOCKER_WEB_EXEC) wp option set blog_public 0

activate-new-theme:
	$(DOCKER_WEB_EXEC) wp theme activate $(THEME_NAME)

delete-twenty-themes:
	cd $(PROJECT_ROOT) \
		&& cd src/wp-content/themes \
		&& rm -rf twenty*

install-new-site:
	$(DOCKER_WEB_EXEC) wp core install --url=$(LOCAL_DOMAIN) --title=$(COMPOSE_PROJECT_NAME) --admin_user=$(ADMIN_USER) --admin_email=$(ADMIN_USER_EMAIL) --prompt=admin_password

install-default-plugins: update-acf-pro
	$(DOCKER_WEB_EXEC) wp plugin install wordfence --activate && \
	$(DOCKER_WEB_EXEC) wp plugin install the-events-calendar --activate && \
	$(DOCKER_WEB_EXEC) wp plugin install relevanssi --activate && \
	$(DOCKER_WEB_EXEC) wp plugin install wordpress-seo --activate && \
	$(DOCKER_WEB_EXEC) wp plugin install tinymce-advanced --activate && \
	$(DOCKER_WEB_EXEC) wp plugin install simple-history --activate && \
	$(DOCKER_WEB_EXEC) wp plugin install google-analyticator --activate && \
	$(DOCKER_WEB_EXEC) wp plugin install mailgun --activate && \
	$(DOCKER_WEB_EXEC) wp plugin install ewww-image-optimizer-cloud --activate && \
	$(DOCKER_WEB_EXEC) wp plugin install autoptimize --activate && \
	$(DOCKER_WEB_EXEC) wp plugin install wp-super-cache --activate && \
	$(DOCKER_WEB_EXEC) wp plugin activate akismet && \
	$(DOCKER_WEB_EXEC) wp plugin delete hello-dolly
	
download-core:
	$(DOCKER_WEB_EXEC) wp core download

# App specific targets go in this file
-include app-targets.mk

# Allow environment specific target overrides
-include env-targets.mk
