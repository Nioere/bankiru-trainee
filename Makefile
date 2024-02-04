PHP := php
COMPOSER := composer
DOCKER_PHP_FPM := docker compose exec php-fpm

up:
	docker compose up -d --remove-orphans

diff:
	$(DOCKER_PHP_FPM) $(PHP) bin/console doctrine:migrations:diff

migrate:
	$(DOCKER_PHP_FPM) $(PHP) bin/console doctrine:migrations:migrate

tests-api:
	docker-compose exec php-fpm sh -c "DATABASE_URL='postgresql://app:!ChangeMe!@database:5432/app' ./vendor/bin/codecept run tests/api/"

composer:
	$(DOCKER_PHP_FPM) $(COMPOSER) install

csfix:
	$(DOCKER_PHP_FPM) $(PHP) -dmemory_limit=-1 vendor/bin/php-cs-fixer fix -vvv --no-ansi

phpstan:
	$(DOCKER_PHP_FPM) $(PHP) -dmemory_limit=-1 vendor/bin/phpstan analyse -c phpstan.neon

