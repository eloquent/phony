<?php

$message = 'Requires non-HHVM runtime (PHP 7).';

return $detector->isSupported('runtime.php') &&
    version_compare(PHP_VERSION, '6.999', '>');
