<?php

if (!$detector->isSupported('runtime.hhvm')) {
    $message = 'Requires HHVM.';

    return false;
}

if (version_compare(HHVM_VERSION, '3.13.x', '<')) {
    $message = 'Requires HHVM >= 3.13.x.';

    return false;
}

return true;
