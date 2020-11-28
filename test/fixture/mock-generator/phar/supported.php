<?php

if (!class_exists('Phar')) {
    $message = 'Requires the Phar class.';

    return false;
}

$message = 'Requires PHP >= 8.0';

return version_compare(PHP_VERSION, '8.0.x', '>=');
