<?php

if (!$detector->isSupported('generator')) {
    $message = 'Requires support for generators.';

    return false;
}

if (!$detector->isSupported('generator.exception')) {
    $message = 'Requires support for generator exceptions.';

    return false;
}

if (!$detector->isSupported('generator.return')) {
    $message = 'Requires support for generator returns.';

    return false;
}

if (!$detector->isSupported('generator.yield.key')) {
    $message = 'Requires support for yielding a key-value pair.';

    return false;
}

return true;
