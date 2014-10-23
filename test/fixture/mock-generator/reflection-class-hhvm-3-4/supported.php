<?php

if (!$detector->isSupported('runtime.hhvm')) {
    $message = 'Requires HHVM.';

    return false;
}

$message = 'Requires non-nightly HHVM 3.4.';

return version_compare(HHVM_VERSION, '3.4.0-dev', '>=');
