<?php

$config = Eloquent\CodeStyle\Config::create(__DIR__);
$config->getFinder()->exclude([
    'artifacts',
    'test/fixture',
]);

return $config;
