<?php

if (!$detector->isSupported('generator')) {
    $message = 'Requires support for generators.';

    return false;
}

if (!$detector->isSupported('generator.return')) {
    $message = 'Requires support for generator returns.';

    return false;
}

return true;
