test: install
	php --version
	vendor/bin/phpunit --no-coverage

coverage: install
	phpdbg --version
	phpdbg -qrr vendor/bin/phpunit

open-coverage:
	open coverage/index.html

lint: install
	vendor/bin/php-cs-fixer fix

install: vendor/autoload.php

examples: install
	doc/example/run-all

edge-cases: install
	php --version
	vendor/bin/phpunit --no-coverage test/suite-edge-cases

benchmarks: install
	vendor/bin/athletic -p test/benchmarks

integration: install
	test/integration/run-all

output-examples: install
	scripts/output-examples

doc-img: install
	scripts/build-doc-img

web: install $(shell find doc assets/web test/fixture/verification)
	make doc-img
	scripts/build-web

open-web:
	open http://localhost:8000/

serve: web
	php -S 0.0.0.0:8000 -t web

publish: web
	@scripts/publish-web

test-fixtures:
	scripts/build-test-fixtures

.PHONY: test coverage open-coverage lint install examples edge-cases benchmarks integration output-examples doc-img open-web serve publish test-fixtures

vendor/autoload.php: composer.lock
	composer install

composer.lock: composer.json
	composer update
