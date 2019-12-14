<?php

$config = Eloquent\CodeStyle\Config::create(__DIR__);
$config->getFinder()->exclude([
    'artifacts',
    'test/fixture',
]);
$config->setRules(array_merge($config->getRules(), [
    'phpdoc_to_comment' => false,
    'no_blank_lines_after_phpdoc' => false,
]));

return $config;
