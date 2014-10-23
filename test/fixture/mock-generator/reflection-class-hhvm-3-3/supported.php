<?php

if (!$detector->isSupported('runtime.hhvm')) {
    $message = 'Requires HHVM.';

    return false;
}

$message = 'Requires HHVM 3.3.';

return $detector->checkMaximumVersion(HHVM_VERSION, '3.3');
