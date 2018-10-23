
all:

style:
	vendor/bin/indent --tabs composer.json

test:
	vendor/bin/phpunit

