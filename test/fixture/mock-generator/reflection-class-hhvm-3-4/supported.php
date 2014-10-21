<?php

$message = 'API difference.';

return defined('HHVM_VERSION') && '3.4' === substr(HHVM_VERSION, 0, 3);
