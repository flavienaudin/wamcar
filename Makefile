USER_ID=`id -u`
USER_GID=`id -g`

LOCAL_NPM_CACHE=`npm config get cache`

LOCALPHP = php

#DOCKERRUN = USER_ID=${USER_ID} docker-compose run --rm
#DOCKERPHP = ${DOCKERRUN} php php
#DOCKERNPM = USER_ID=${USER_ID} USER_GID=${USER_GID} docker-compose run --rm -v "$(LOCAL_NPM_CACHE):/.npm" -v "$(PWD):/usr/src/app:cached" npm npm

# Help
.SILENT:
.PHONY: help

help: ## Display this help
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

# Deploy
#staging: front
#	./bin/dep deploy staging

# Setup dev
#dev-back: web-up vendors database ## setup a dev environnement (backend only)
#dev: dev-back front ## setup a dev environnement

# Docker
#web-up: ## start docker services to run dev website
#	@echo "--> Starting docker nginx service and its dependencies"
#	USER_ID=${USER_ID} docker-compose up -d nginx
#phantom-up: ## start docker services to run phantomjs stack
#	@echo "--> Starting docker nginx service and its dependencies"
#	USER_ID=${USER_ID} docker-compose up -d phantomjs

# Composer vendors
vendors: vendor/autoload.php ## install up to date packages with dockerized composer (if needed)
vendor/autoload.php: composer.lock
	@echo "--> Installing composer packages"
	composer install --no-scripts --no-suggest --optimize-autoloader -vvv
composer.lock: composer.json
	@echo "--> Updating composer packages"
	composer update --no-scripts --no-suggest --optimize-autoloader -vvv

# Database management
database: migration

migration:
	@echo "--> Migrating database if needed"
	$(LOCALPHP) ./bin/console doctrine:migration:migrate --no-interaction --allow-no-migration

liip-cache-remove:
	@echo "--> Remove liipImagine cache"
	$(LOCALPHP) ./bin/console liip:imagine:cache:remove

# Frontend asset management
npm-install: package-lock.json
	@echo "--> Installing NPM packages"
	npm install
start-front: npm-install ## start front dev watcher
	@echo "--> Starting npm dev service"
	npm run start
front: npm-install ## build prod front
	@echo "--> Building frontend production assets"
	npm run build
