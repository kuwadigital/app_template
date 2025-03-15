PROJECT_SRC= $(CURDIR)/app
PROJECT_SQL_FILE= $(CURDIR)/db

include $(shell find ${CURDIR} -maxdepth 1 -name '.env*' ! -name '*.dist' | sort -n )

# Executables (local)
DOCKER_COMP = docker compose

# Docker containers
USER_FLAG   = --user=$(shell id -u):$(shell id -g)
PHP_CONT    = $(DOCKER_COMP) exec $(USER_FLAG) php-fpm

# Executables
PHP      = $(PHP_CONT) php
CONSOLE  = @$(PHP) bin/console
PHPUNIT  = @$(PHP) bin/phpunit
COMPOSER = $(PHP_CONT) composer

# Misc
.DEFAULT_GOAL = help

##
##—————————————————————————————— The Symfony Docker Makefile
help: ## Outputs this help screen
	@grep -E '(^[a-zA-Z0-9\./_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

##—————————————————————————————— App
app-build: ## Install App
	@$(DOCKER_COMP) build --pull #--no-cache
	@$(DOCKER_COMP) up --detach
	@$(COMPOSER) install

app-shell: ## Bash
	@$(PHP_CONT) bash

app-composer: ## Run composer command (Example: make composer c='req symfony/uid')
	@$(COMPOSER) $(c)

app-test: ## Run all tests (Example specify: make test c=tests/Functional)
	@$(PHPUNIT) $(c)

app-console: ## Run console (Example: make console c=make:controller)
	@$(CONSOLE) $(c)

app-clean: ## Clear App cache (env=dev)
	@$(CONSOLE) cache:clear --env=dev

app-route: ## Lists all your application routes
	@$(CONSOLE) debug:route

app-db-import: app-db-import-mysql


app-db-available-mysql:
	@echo "CHECK AVAILABILITY OF MYSQL-SERVICE ..."
	@while ! (docker-compose exec -T ${MYSQL_SERVICE} mysql -u ${MYSQL_USER} -p${MYSQL_PASSWORD} --execute='show databases;'); do \
		sleep 1; \
		WAIT=$$(($$WAIT + 1));  \
		echo  ">> Mysql service is not available - Waiting $${WAIT}s";  \
		if [ "$$WAIT" -gt 120 ]; then  \
			echo ">> Error: Timeout 120s by waiting for Mysql service";  \
			exit 1;  \
		fi  \
	done
	@echo ">> Mysql service is now available"
	@sleep 2;
	@echo
	@echo


app-db-import-mysql: app-db-available-mysql
	@echo "IMPORT MYSQL-DATABASE ..."
	@for dbName in ${MYSQL_DATABASE}; do  \
			if [ -f "${PROJECT_SQL_FILE}/$$dbName.sql" ]; then \
				echo ">> Import database ${PROJECT_SQL_FILE}/$$dbName.sql on ${MYSQL_SERVICE}"; \
				docker-compose exec -T  ${MYSQL_SERVICE} sh -c "mysqldump -u${MYSQL_USER} -p${MYSQL_PASSWORD} --no-data --add-drop-table $$dbName | grep ^DROP | mysql --init-command='SET SESSION FOREIGN_KEY_CHECKS=0;' -u${MYSQL_USER} -p${MYSQL_PASSWORD} -v $$dbName";  \
				docker-compose exec -T  ${MYSQL_SERVICE} mysql --init-command='SET SESSION FOREIGN_KEY_CHECKS=0;' -u root -p${MYSQL_ROOT_PASSWORD} --force $$dbName < "${PROJECT_SQL_FILE}/$$dbName.sql"; \
			fi \
	done
	@echo
	@echo ">> Import proccess is finished"
	@echo
	@echo


app-db-export: app-db-export-mysql

app-db-export-mysql:
	@echo "EXPORT MYSQL-DATABASE ..."
	@for dbName in ${MYSQL_DATABASE}; do \
		docker-compose exec -T  ${MYSQL_SERVICE} mysqldump --single-transaction -u${MYSQL_USER} -p${MYSQL_PASSWORD} $$dbName > "${PROJECT_SQL_FILE}/$$dbName.sql"; \
	done
	@echo ">> Export proccess is finished"
	@echo
	@echo


##—————————————————————————————— Docker
docker-restart: stop start ## Restart the Docker containers (stop start)
docker-rebuild: down build up ## Rebuild the Docker containers (down build up)

docker-build: ## Build Docker images
	@$(DOCKER_COMP) build

docker-up: ## Start Docker containers in detached mode
	@$(DOCKER_COMP) up --detach

docker-down: ## Stop and remove Docker containers and orphaned volumes
	@$(DOCKER_COMP) down --remove-orphans

docker-start: ## Start Docker containers
	@${DOCKER_COMP} start

docker-stop: ## Stop Docker containers
	@${DOCKER_COMP} stop

##—————————————————————————————— Static code analysis of our system
sys-phpstan: ## Run the static analysis of code.
	@${PHP_CONT} vendor/bin/phpstan analyse -c phpstan.neon
	@${PHP_CONT} vendor/bin/phpstan clear-result-cache

sys-cs-diff: ## Show coding standards problems (without making changes)
	@${PHP_CONT} vendor/bin/php-cs-fixer fix --dry-run --diff

sys-cs-fix: ## Fix as much coding standards problems
	@${PHP_CONT} vendor/bin/php-cs-fixer fix

sys-composer-validate: ## Validate your composer.json file
	${PHP_CONT} composer validate
