
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

# find spec classes that are not mentioned in tests with @covers yet
coverage:
	grep -rhPo '@covers .+' tests |cut -c 28- |sort > /tmp/php-openapi-covA
	grep -rhPo 'class \w+' src/spec/ | awk '{print $$2}' |grep -v '^Type$$' | sort > /tmp/php-openapi-covB
	diff /tmp/php-openapi-covB /tmp/php-openapi-covA


.PHONY: all check-style fix-style install test

