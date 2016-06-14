<?php

if (!$featureDetector->isSupported('generator')) {
    $message = 'Requires support for generators.';

    return false;
}

if (!$featureDetector->isSupported('generator.return')) {
    $message = 'Requires support for generator returns.';

    return false;
}

return true;
