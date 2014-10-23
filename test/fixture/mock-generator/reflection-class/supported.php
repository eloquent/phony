<?php

if (defined('HHVM_VERSION')) {
    $message = 'Requires non-HHVM runtime';

    return false;
}

$message = 'Requires no trait support.';

return !$detector->isSupported('trait');
