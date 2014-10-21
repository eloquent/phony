<?php

$message = 'Requires callable type hint support.';

return version_compare(PHP_VERSION, '5.4.0-dev', '>=');
