<?php

if (!class_exists('Phar')) {
    $message = 'Requires the Phar class.';

    return false;
}

if (version_compare(PHP_VERSION, '8.1.x', '<')) {
    $message = 'Requires PHP >= 8.1';

    return false;
}

$setStub = new ReflectionMethod('Phar', 'setStub');
list(, $length) = $setStub->getParameters();
$message = "Requires Phar::setStub()'s len parameter's default value to be unavailable";

return !$length->isDefaultValueAvailable();
