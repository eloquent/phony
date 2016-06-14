<?php

if (!$featureDetector->isSupported('generator')) {
    $message = 'Requires support for generators.';

    return false;
}

if ($featureDetector->isSupported('runtime.hhvm')) {
    $message = 'Requires non-HHVM generators.';

    return false;
}

return true;
