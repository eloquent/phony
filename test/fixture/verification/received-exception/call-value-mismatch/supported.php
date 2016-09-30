<?php

if (!$detector->isSupported('generator')) {
    $message = 'Requires support for generators.';

    return false;
}

if ($detector->isSupported('runtime.hhvm')) {
    $message = 'Requires non-HHVM generators.';

    return false;
}

return true;
