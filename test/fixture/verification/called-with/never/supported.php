<?php

$message = 'Requires PHP >= 7.4';

return version_compare(PHP_VERSION, '7.4.x', '>=');
