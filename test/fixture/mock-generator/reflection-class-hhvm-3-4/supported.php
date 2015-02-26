<?php

if (!$detector->isSupported('runtime.hhvm')) {
    $message = 'Requires HHVM.';

    return false;
}

$message = 'Requires HHVM 3.4.';

return version_compare(HHVM_VERSION, '3.4.x', '>=') &&
    version_compare(HHVM_VERSION, '3.5.x', '<');
