<?php

$message = 'API difference.';

return defined('HHVM_VERSION') && '3.3' === substr(HHVM_VERSION, 0, 3);
