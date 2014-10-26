<?php

if (defined('HHVM_VERSION')) {
    $message = 'Requires non-HHVM runtime.';

    return false;
}

$message = 'Requires traits.';

return $detector->isSupported('trait');
