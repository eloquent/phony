<?php

if (defined('HHVM_VERSION')) {
    $message = 'Requires non-HHVM runtime.';

    return false;
}

return true;
