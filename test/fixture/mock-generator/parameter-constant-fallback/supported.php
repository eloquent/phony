<?php

$message = 'Only applicable where ReflectionParameter does not support constant names.';

return defined('HHVM_VERSION') ||
    version_compare(PHP_VERSION, '5.4.0-dev', '<');
