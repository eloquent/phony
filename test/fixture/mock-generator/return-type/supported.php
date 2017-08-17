<?php

if ($detector->isSupported('runtime.hhvm')) {
    $message = 'HHVM scalar type hints are bugged.';

    return false;
}

return true;
