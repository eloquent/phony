<?php

if (!$featureDetector->isSupported('generator')) {
    $message = 'Requires support for generators.';

    return false;
}

if (!$featureDetector->isSupported('generator.exception')) {
    $message = 'Requires support for generator exceptions.';

    return false;
}

if (!$featureDetector->isSupported('generator.return')) {
    $message = 'Requires support for generator returns.';

    return false;
}

if (!$featureDetector->isSupported('generator.yield.key')) {
    $message = 'Requires support for yielding a key-value pair.';

    return false;
}

return true;
