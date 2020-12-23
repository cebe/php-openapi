TESTCASE=
XDEBUG=0
PHPARGS=-dmemory_limit=512M
XPHPARGS=
ifeq ($(XDEBUG),1)
XPHPARGS=-dzend_extension=xdebug.so -dxdebug.remote_enable=1 -dxdebug.remote_autostart=1
endif

all:

check-style: php-cs-fixer.phar
	PHP_CS_FIXER_IGNORE_ENV=1 ./php-cs-fixer.phar fix src/ --diff --dry-run

fix-style: php-cs-fixer.phar
	vendor/bin/indent --tabs composer.json
	vendor/bin/indent --spaces .php_cs.dist
	./php-cs-fixer.phar fix src/ --diff

install:
	composer install --prefer-dist --no-interaction --no-progress --ansi
	yarn install

test:
	php $(PHPARGS) $(XPHPARGS) vendor/bin/phpunit --verbose --colors=always $(TESTCASE)
	php $(PHPARGS) $(XPHPARGS) bin/php-openapi validate tests/spec/data/recursion.json
	php $(PHPARGS) $(XPHPARGS) bin/php-openapi validate tests/spec/data/recursion2.yaml

lint:
	php $(PHPARGS) $(XPHPARGS) bin/php-openapi validate tests/spec/data/reference/playlist.json
	php $(PHPARGS) $(XPHPARGS) bin/php-openapi validate tests/spec/data/recursion.json
	php $(PHPARGS) $(XPHPARGS) bin/php-openapi validate tests/spec/data/recursion2.yaml
	node_modules/.bin/speccy lint tests/spec/data/reference/playlist.json
	node_modules/.bin/speccy lint tests/spec/data/recursion.json

stan:
	php $(PHPARGS) vendor/bin/phpstan analyse -l 5 src

# copy openapi3 json schema
schemas/openapi-v3.0.json: vendor/oai/openapi-specification/schemas/v3.0/schema.json
	cp $< $@

schemas/openapi-v3.0.yaml: vendor/oai/openapi-specification/schemas/v3.0/schema.yaml
	cp $< $@

php-cs-fixer.phar:
	wget -q https://github.com/FriendsOfPHP/PHP-CS-Fixer/releases/download/v2.16.7/php-cs-fixer.phar && chmod +x php-cs-fixer.phar

# find spec classes that are not mentioned in tests with @covers yet
coverage: .php-openapi-covA .php-openapi-covB
	diff $^
.INTERMEDIATE: .php-openapi-covA .php-openapi-covB
.php-openapi-covA:
	grep -rhPo '@covers .+' tests |cut -c 28- |sort > $@
.php-openapi-covB:
	grep -rhPo '^class \w+' src/spec/ | awk '{print $$2}' |grep -v '^Type$$' | sort > $@

.PHONY: all check-style fix-style install test lint coverage

