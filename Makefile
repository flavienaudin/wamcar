USER_ID=`id -u`
USER_GID=`id -g`

LOCAL_NPM_CACHE=`npm config get cache`

DOCKERRUN = USER_ID=${USER_ID} docker-compose run --rm --user="${USER_ID}"
DOCKERPHP = ${DOCKERRUN} php php
DOCKERNPM = USER_ID=${USER_ID} USER_GID=${USER_GID} docker-compose run --rm -v "$(LOCAL_NPM_CACHE):/.npm" -v "$(PWD):/usr/src/app:cached" npm npm

# Help
.SILENT:
.PHONY: help

help: ## Display this help
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

# Deploy
staging: front
	./bin/dep deploy staging

# Setup dev
dev-back: web-up vendors database ## setup a dev environnement (backend only)
dev: dev-back front ## setup a dev environnement

# Docker
web-up: ## start docker services to run dev website
	@echo "--> Starting docker nginx service and its dependencies"
	USER_ID=${USER_ID} docker-compose up -d nginx
phantom-up: ## start docker services to run phantomjs stack
	@echo "--> Starting docker nginx service and its dependencies"
	USER_ID=${USER_ID} docker-compose up -d phantomjs

# Composer vendors
vendors: vendor/autoload.php ## install up to date packages with dockerized composer (if needed)
vendor/autoload.php: composer.lock
	@echo "--> Installing composer packages"
	$(DOCKERRUN) composer install --no-scripts --no-suggest --optimize-autoloader
composer.lock: composer.json
	@echo "--> Updating composer packages"
	$(DOCKERRUN) composer update --no-scripts --no-suggest --optimize-autoloader

# Database management
database: fixtures
fixtures: migration
	@echo "--> Loading fixtures in database"
	$(DOCKERPHP) ./bin/console doctrine:fixtures:load --no-interaction --fixtures=./database/fixtures
migration:
	@echo "--> Migrating database if needed"
	$(DOCKERPHP) ./bin/console doctrine:migration:migrate --no-interaction --allow-no-migration
vehicle-fixtures: database/fixtures/base_vehicule_short.csv
	@echo "--> Populate vehicle index"
	$(DOCKERPHP) ./bin/console wamcar:populate:vehicle_info

# Frontend asset management
NPM_OUT = node_modules/npm.md5
npm-install: $(NPM_OUT) ## install NPM packages from shrinkwrap file
$(NPM_OUT): package-lock.json
	@echo "--> Installing NPM packages"
	$(DOCKERNPM) install
	-@md5 npm-shrinkwrap.json > $(NPM_OUT)
	-@md5sum npm-shrinkwrap.json > $(NPM_OUT)
front-start: npm-install ## start front dev watcher
	@echo "--> Starting npm dev service"
	npm start
front-live: npm-install ## run live reloading proxy
	@echo "--> Starting npm dev service"
	npm run proxy
front: npm-install ## build prod front
	@echo "--> Building frontend production assets"
	$(DOCKERNPM) run build

# Testing
test: dev test-unit test-behavior
test-unit:
	@echo "--> Running unit test suites"
	$(DOCKERPHP) ./bin/atoum
test-behavior: web-up phantom-up database
	@echo "--> Preparing cache for behavior tests"
	rm -rf ./var/cache/*
	rm -rf ./var/logs/*
	-$(DOCKERRUN) php sh -c "setfacl -dR -m u:www-data:rwX /var/www/myapp/var"
	-$(DOCKERRUN) php sh -c "setfacl -R -m u:www-data:rwX /var/www/myapp/var"
	$(DOCKERPHP) ./bin/console cache:clear --env="test"
	@echo "--> Running behavior test suites"
	$(DOCKERPHP) ./bin/behat
