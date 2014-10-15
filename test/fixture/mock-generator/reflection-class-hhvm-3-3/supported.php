<?php

return defined('HHVM_VERSION') &&
    version_compare(HHVM_VERSION, '3.3.0-dev', '>=') &&
    version_compare(HHVM_VERSION, '3.4.0-dev', '<');
