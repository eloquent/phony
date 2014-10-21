<?php

if (!defined('HHVM_VERSION') || '3.4' !== substr(HHVM_VERSION, 0, 3)) {
    $message = 'Requires HHVM 3.4.';

    return false;
}

$message = 'Requires non-nightly HHVM.';

return version_compare(HHVM_VERSION, '3.4.0-dev', '>=');
