<?php

if (defined('HHVM_VERSION')) {
    $message = 'Requires non-HHVM runtime';

    return false;
}

$message = 'Requires no trait support.';

return version_compare(PHP_VERSION, '5.4.0-dev', '<');
