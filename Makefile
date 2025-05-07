TESTCASE=
XDEBUG=0
PHPARGS=-dmemory_limit=512M
XPHPARGS=
ifeq ($(XDEBUG),1)
XPHPARGS=-dzend_extension=xdebug.so -dxdebug.remote_enable=1 -dxdebug.remote_autostart=1
endif

# Run make with IN_DOCKER=1 to run yarn and php commands in a docker container
DOCKER_PHP=
DOCKER_NODE=
IN_DOCKER=0
ifeq ($(IN_DOCKER),1)
DOCKER_PHP=docker-compose run --rm php
DOCKER_NODE=docker-compose run --rm -w /app node
endif

all:
	@echo "the following commands are available:"
	@echo ""
	@echo "make check-style              # check code style"
	@echo "make fix-style                # fix code style"
	@echo "make install                  # install dependencies"
	@echo "make test                     # run PHPUnit tests"
	@echo "make lint                     # check validity of test data"
	@echo "make stan                     # check code with PHPStan"
	@echo ""
	@echo "You may add the IN_DOCKER parameter to run a command inside of docker container and not directly."
	@echo "make IN_DOCKER=1 ..."


check-style: php-cs-fixer.phar
	PHP_CS_FIXER_IGNORE_ENV=1 ./php-cs-fixer.phar fix src/ --diff --dry-run

fix-style: php-cs-fixer.phar
	$(DOCKER_PHP) vendor/bin/indent --tabs composer.json
	$(DOCKER_PHP) vendor/bin/indent --spaces .php_cs.dist
	$(DOCKER_PHP) ./php-cs-fixer.phar fix src/ --diff

cli:
	docker-compose run --rm php bash

install: composer.json package.json
	$(DOCKER_PHP) composer install --prefer-dist --no-interaction --no-progress --ansi
	$(DOCKER_NODE) yarn install

test: unit test-recursion.json test-recursion2.yaml test-recursion3_index.yaml test-empty-maps.json

unit:
	$(DOCKER_PHP) php $(PHPARGS) $(XPHPARGS) vendor/bin/phpunit --verbose --colors=always $(TESTCASE)

# test specific JSON files in tests/spec/data/
# e.g. test-recursion will run validation on tests/spec/data/recursion.json
test-%: tests/spec/data/%
	$(DOCKER_PHP) php $(PHPARGS) $(XPHPARGS) bin/php-openapi validate $<

lint: install
	$(DOCKER_PHP) php $(PHPARGS) $(XPHPARGS) bin/php-openapi validate tests/spec/data/reference/playlist.json
	$(DOCKER_PHP) php $(PHPARGS) $(XPHPARGS) bin/php-openapi validate tests/spec/data/recursion.json
	$(DOCKER_PHP) php $(PHPARGS) $(XPHPARGS) bin/php-openapi validate tests/spec/data/recursion2.yaml
	$(DOCKER_PHP) php $(PHPARGS) $(XPHPARGS) bin/php-openapi validate tests/spec/data/empty-maps.json
	$(DOCKER_NODE) yarn run speccy lint tests/spec/data/reference/playlist.json
	$(DOCKER_NODE) yarn run speccy lint tests/spec/data/recursion.json

stan:
	$(DOCKER_PHP) php $(PHPARGS) vendor/bin/phpstan analyse -l 5 src

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
