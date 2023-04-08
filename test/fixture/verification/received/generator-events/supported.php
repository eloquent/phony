<?php

$message = 'Requires PHP >= 8.1';

return version_compare(PHP_VERSION, '8.1.x', '>=');
