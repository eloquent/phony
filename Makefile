.PHONY: test
test: install
	php --version
	PHP_ERROR_EXCEPTION_DEPRECATIONS=1 vendor/bin/phpunit --no-coverage

.PHONY: test-hhvm
test-hhvm: install
	test/bin/hhvm-3.21 sh -c "php --version; PHP_ERROR_EXCEPTION_DEPRECATIONS=1 php -d 'hhvm.php7.all=1' -d 'hhvm.hack.lang.look_for_typechecker=0' vendor/bin/phpunit --no-coverage"

.PHONY: coverage
coverage: install
	phpdbg --version
	PHP_ERROR_EXCEPTION_DEPRECATIONS=1 phpdbg -qrr vendor/bin/phpunit

.PHONY: open-coverage
open-coverage:
	open coverage/index.html

.PHONY: lint
lint: test/bin/php-cs-fixer
	test/bin/php-cs-fixer fix --using-cache no

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
	php -S 0.0.0.0:8000 -t web

.PHONY: publish
publish: web
	@scripts/publish-web

.PHONY: test-fixtures
test-fixtures:
	scripts/build-test-fixtures

test/bin/php-cs-fixer:
	curl -sSL http://cs.sensiolabs.org/download/php-cs-fixer-v2.phar -o test/bin/php-cs-fixer
	chmod +x test/bin/php-cs-fixer
