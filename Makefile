.PHONY: test
test: install
	php --version
	PHP_ERROR_EXCEPTION_DEPRECATIONS=1 vendor/bin/phpunit --no-coverage

.PHONY: coverage
coverage: install
	phpdbg --version
	PHP_ERROR_EXCEPTION_DEPRECATIONS=1 phpdbg -qrr vendor/bin/phpunit

.PHONY: open-coverage
open-coverage:
	open coverage/index.html

.PHONY: lint
lint: install
	vendor/bin/php-cs-fixer fix

.PHONY: install
install:
	composer install

.PHONY: edge-cases
edge-cases: install
	php --version
	vendor/bin/phpunit --no-coverage test/suite-edge-cases

.PHONY: integration
integration: install
	test/integration/run-all

.PHONY: output-examples
output-examples: install
	scripts/output-examples

.PHONY: doc-img
doc-img: install
	scripts/build-doc-img

web: install $(shell find doc assets/web test/fixture/verification)
	make doc-img
	scripts/build-web

.PHONY: open-web
open-web:
	open http://localhost:8000/

.PHONY: serve
serve: web
	php -S 0.0.0.0:8000 -t web assets/scripts/documentation-router.php

.PHONY: publish
publish: web
	@scripts/publish-web

.PHONY: test-fixtures
test-fixtures:
	scripts/build-test-fixtures
