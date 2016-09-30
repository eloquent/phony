<?php

if (!$detector->isSupported('generator')) {
    $message = 'Requires support for generators.';

    return false;
}

return true;
