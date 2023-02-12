# Powered by https://makefiles.dev/

export PHP_ERROR_EXCEPTION_DEPRECATIONS=1

################################################################################

_HOOK_FIXTURE_INPUT_FILES := $(shell find test/fixture/hook-generator -name callback.php)
_HOOK_FIXTURE_OUTPUT_FILES := $(_HOOK_FIXTURE_INPUT_FILES:callback.php=expected.php)

_MOCK_FIXTURE_INPUT_FILES := $(shell find test/fixture/mock-generator -name builder.php)
_MOCK_FIXTURE_OUTPUT_FILES := $(_MOCK_FIXTURE_INPUT_FILES:builder.php=expected.php)

_VERIFICATION_FIXTURE_INPUT_FILES := $(shell find test/fixture/verification -name verification.php)
_VERIFICATION_FIXTURE_OUTPUT_FILES := $(_VERIFICATION_FIXTURE_INPUT_FILES:verification.php=expected)
_VERIFICATION_IMAGE_FILES := $(_VERIFICATION_FIXTURE_INPUT_FILES:test/fixture/verification/%/verification.php=artifacts/build/doc-img/%.svg)

_DOC_MARKDOWN_FILES := $(wildcard doc/*.md)
_DOC_HTML_FILES := $(_DOC_MARKDOWN_FILES:doc/%.md=artifacts/build/doc-html/%.html)

GENERATED_FILES += $(_HOOK_FIXTURE_OUTPUT_FILES) $(_MOCK_FIXTURE_OUTPUT_FILES) $(_VERIFICATION_FIXTURE_OUTPUT_FILES)
CI_VERIFY_GENERATED_FILES :=

################################################################################

-include .makefiles/Makefile
-include .makefiles/pkg/php/v1/Makefile

.makefiles/%:
	@curl -sfL https://makefiles.dev/v1 | bash /dev/stdin "$@"

################################################################################

.PHONY: doc
doc: artifacts/build/gh-pages

.PHONY: doc-open
doc-open:
	open http://localhost:8080/

.PHONY: doc-publish
doc-publish: artifacts/build/gh-pages
	scripts/publish-doc "$<"

.PHONY: doc-serve
doc-serve: artifacts/build/gh-pages
	php -S 0.0.0.0:8080 -t "$<" assets/router.php

.PHONY: output-examples
output-examples: vendor
	scripts/output-examples

.PHONY: test-edge-cases
test-edge-cases: artifacts/test/edge-cases.touch

.PHONY: test-integration
test-integration: artifacts/test/integration.touch

################################################################################

artifacts/build/doc-html/%.html: doc/%.md vendor $(wildcard assets/web/*.tpl.html)
	@mkdir -p "$(@D)"
	scripts/gfm-to-html "$<" "$@"

artifacts/build/doc-img/%.svg: test/fixture/verification/%/verification.php $(wildcard assets/svg/*.tpl.svg) vendor $(PHP_SOURCE_FILES)
	@mkdir -p "$(@D)"
	scripts/build-doc-img "$<" "$@"

artifacts/build/doc: $(wildcard assets/web/css/* assets/web/data/* assets/web/img/* assets/web/js/*) $(_DOC_HTML_FILES) $(_VERIFICATION_IMAGE_FILES)
	@rm -rf "$@"
	@mkdir -p "$@/img/verification"
	@cp -av assets/web/css assets/web/data assets/web/img assets/web/js artifacts/build/doc-html/*.html "$@/"
	@cp -av artifacts/build/doc-img/* "$@/img/verification/"

artifacts/build/gh-pages: artifacts/build/doc artifacts/build/gh-pages-clone vendor $(wildcard assets/web/*.tpl.html)
	scripts/refresh-git-clone artifacts/build/gh-pages-clone
	@rm -rf "$@"
	cp -a artifacts/build/gh-pages-clone "$@"
	scripts/update-gh-pages "$<" "$@"

artifacts/build/gh-pages-clone:
	git clone -b gh-pages --single-branch --depth 1 https://github.com/eloquent/phony.git "$@"

artifacts/test/edge-cases.touch: $(PHP_PHPUNIT_REQ) $(_PHP_PHPUNIT_REQ)
	php $(_PHP_PHPUNIT_RUNTIME_ARGS) vendor/bin/phpunit $(_PHP_PHPUNIT_ARGS) --no-coverage test/suite-edge-cases

	@mkdir -p "$(@D)"
	@touch "$@"

artifacts/test/integration.touch: vendor $(PHP_SOURCE_FILES) $(_PHP_TEST_ASSETS)
	test/integration/run-all

	@mkdir -p "$(@D)"
	@touch "$@"

test/fixture/hook-generator/%/expected.php: | test/fixture/hook-generator/%/callback.php
	scripts/build-hook-generator-fixture "$|" "$@"

test/fixture/mock-generator/%/expected.php: | test/fixture/mock-generator/%/builder.php
	scripts/build-mock-generator-fixture "$|" "$@"

test/fixture/verification/%/expected: | test/fixture/verification/%/verification.php
	scripts/build-verification-fixture "$|" "$@"
