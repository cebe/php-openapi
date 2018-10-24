
all:

style:
	vendor/bin/indent --tabs composer.json

install:
	composer install --prefer-dist --no-interaction

test:
	vendor/bin/phpunit

.PHONY: all style install test

