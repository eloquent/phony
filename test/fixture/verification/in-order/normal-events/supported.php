<?php

$message = 'Requires PHP >= 8.2';

return version_compare(PHP_VERSION, '8.2.x', '>=');
