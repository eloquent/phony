<?php

if (!class_exists('Phar')) {
    $message = 'Requires the Phar class.';

    return false;
}

if (
    !$detector->isSupported('runtime.php') ||
    version_compare(PHP_VERSION, '7.x', '<')
) {
    $message = 'Requires PHP 7.';

    return false;
}

return true;
