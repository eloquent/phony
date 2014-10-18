#!/usr/bin/env bash
set -e

cp composer.json composer.json.tmp
cp composer.lock composer.lock.tmp

if php -r "exit(version_compare(phpversion(), '5.4.0-dev', '>=') ? 0 : 1);"; then
    composer require --dev counterpart/counterpart:~1.5.0
fi

cp composer.json.tmp composer.json
cp composer.lock.tmp composer.lock
rm composer.{json,lock}.tmp
