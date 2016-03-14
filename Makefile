test: install
	php --version
	vendor/bin/phpunit --no-coverage

coverage: install
	phpdbg --version
	phpdbg -qrr vendor/bin/phpunit

lint: install
	vendor/bin/php-cs-fixer fix

install: vendor/autoload.php

examples:
	doc/example/run-all

benchmarks:
	vendor/bin/athletic -p test/benchmarks

integration:
	test/integration/run-all

web: $(shell find doc assets/web)
	scripts/build-web

publish: web
	vendor/bin/woodhouse publish eloquent/phony --auth-token $(GITHUB_TOKEN) \
        web/index.html:index.html \
        web/css:css \
        web/img:img \
        web/js:js

.PHONY: test coverage lint install examples benchmarks integration publish

vendor/autoload.php: composer.lock
	composer install

composer.lock: composer.json
	composer update
