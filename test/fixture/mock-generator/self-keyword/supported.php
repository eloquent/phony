<?php

$message = 'Requires parameter constant name support in ReflectionParameter.';

return !defined('HHVM_VERSION') &&
    version_compare(PHP_VERSION, '5.4.0-dev', '>=');
