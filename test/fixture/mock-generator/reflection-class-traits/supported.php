<?php

$message = 'Requires traits.';

if (!$detector->isSupported('trait')) {
    return false;
}

$message = 'Requires non-HHVM runtime.';

if (defined('HHVM_VERSION')) {
    return false;
}

$message = 'Requires non-HHVM runtime (less than PHP 7).';

return version_compare(PHP_VERSION, '7.x', '<');
