<?php

if (!$featureDetector->isSupported('generator')) {
    $message = 'Requires support for generators.';

    return false;
}

return true;
