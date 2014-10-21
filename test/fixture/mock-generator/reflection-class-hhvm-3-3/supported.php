<?php

$message = 'Requires HHVM 3.3.';

return defined('HHVM_VERSION') && '3.3' === substr(HHVM_VERSION, 0, 3);
