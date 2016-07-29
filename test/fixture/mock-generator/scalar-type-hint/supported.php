<?php

if ($detector->isSupported('runtime.hhvm')) {
    $message = 'HHVM scalar type hints are bugged.';

    return false;
}

$message = 'Requires scalar type hints.';

return $detector->isSupported('parameter.hint.scalar');
