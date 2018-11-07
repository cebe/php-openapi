
all:

check-style:
	vendor/bin/php-cs-fixer fix src/ --diff --dry-run

fix-style:
	vendor/bin/indent --tabs composer.json
	vendor/bin/indent --spaces .php_cs.dist
	vendor/bin/php-cs-fixer fix src/ --diff

install:
	composer install --prefer-dist --no-interaction

test:
	vendor/bin/phpunit

.PHONY: all check-style fix-style install test

