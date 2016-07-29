<?php

if ($detector->isSupported('runtime.hhvm')) {
    $message = 'HHVM scalar type hints are bugged.';

    return false;
}

$message = 'Requires return type declarations.';

return $detector->isSupported('return.type');
