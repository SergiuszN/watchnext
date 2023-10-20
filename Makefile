# Executables (local)
DOCKER_COMP = docker-compose

# Docker containers
PHP_CONT = $(DOCKER_COMP) exec php
NODE_CONT = $(DOCKER_COMP) exec node
PHP_RUN = $(DOCKER_COMP) run php

# Executables
PHP      = $(PHP_CONT) php
COMPOSER = $(PHP_CONT) composer
SYMFONY  = $(PHP_CONT) bin/console
TESTS	 = $(PHP_CONT) bin/phpunit
NPM	     = $(NODE_CONT) npm

# Misc
.DEFAULT_GOAL = help
.PHONY        = help build up start down logs sh composer vendor sf cc

## —— 🎵 🐳 The Symfony-docker Makefile 🐳 🎵 ——————————————————————————————————
help: ## Outputs this help screen
	@grep -E '(^[a-zA-Z0-9_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

## —— Docker 🐳 ————————————————————————————————————————————————————————————————
build: ## Builds the Docker images
	@$(DOCKER_COMP) build --pull --no-cache

up: ## Start the docker hub in detached mode (no logs)
	@$(DOCKER_COMP) up --detach

down: ## Stop the docker hub
	@$(DOCKER_COMP) down --remove-orphans

stop:
	@$(DOCKER_COMP) stop

logs: ## Show live logs
	@$(DOCKER_COMP) logs --tail=0 --follow

sh: ## Connect to the PHP FPM container
	@$(PHP_CONT) bash

node:
	@$(NODE_CONT) bash

## —— Project ————————————————————————————————————————————————————————————————
pull: ## Build project after pull
	@$(PHP_CONT) sh -c "\
			composer install; \
		"

npminstall:
	@$(NODE_CONT) sh -c "\
			npm install; \
		"

npmwatch:
	@$(NODE_CONT) sh -c "\
			npm run watch; \
		"

npmbuild:
	@$(NODE_CONT) sh -c "\
			npm run build; \
		"

trans:
	@$(PHP_CONT) sh -c "\
			php console.php translations:check --base=en; \
			php console.php translations:reorder; \
		"

#push: ## Check project before push
#	$(MAKE)	fix-cs
#	$(MAKE) dep-trac
#
#dsu: ## Build project after pull
#	@$(DOCKER_COMP) exec php sh -c "\
#			php bin/console d:s:u --dump-sql --force --complete; \
#		"
#
#fix-cs: ## Fixes coding style
#	docker-compose exec php sh -c "\
#    		vendor/friendsofphp/php-cs-fixer/php-cs-fixer fix; \
#    	"

## —— Tests 🧪 ———————————————————————————————————————————————————————————————
#test: ## Run php unit on existing container
#	docker-compose exec php sh -c "\
#		export APP_ENV='test'; \
#        vendor/bin/behat; \
#		php bin/phpunit; \
#	"

## —— DepTrac 🔗 ———————————————————————————————————————————————————————————————
#dep-trac: ## Run DepTrac on existing container
#	docker-compose exec php sh -c "\
#            vendor/bin/deptrac analyse --config-file=deptrac.yaml \
#    	"