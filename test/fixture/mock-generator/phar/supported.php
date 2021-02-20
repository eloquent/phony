<?php

if (!class_exists('Phar')) {
    $message = 'Requires the Phar class.';

    return false;
}

if (version_compare(PHP_VERSION, '8.0.x', '<')) {
    $message = 'Requires PHP >= 8.0';

    return false;
}

if (false !== strpos(PHP_VERSION, '-')) {
    $message = 'Requires a stable PHP version';

    return false;
}

$setStub = new ReflectionMethod('Phar', 'setStub');
list(, $length) = $setStub->getParameters();
$message = "Requires Phar::setStub()'s len parameter's default value to be unavailable";

return !$length->isDefaultValueAvailable();
