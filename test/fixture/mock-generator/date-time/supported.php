<?php

$message = 'Requires non-HHVM runtime (less than PHP 7).';

return $detector->isSupported('runtime.php') &&
    version_compare(PHP_VERSION, '7.x', '<');
