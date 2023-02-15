<?php

$config = Eloquent\CodeStyle\Config::create(__DIR__);
$config->setCacheFile(__DIR__ . '/artifacts/lint/php-cs-fixer/cache');
$config->setRules(array_merge($config->getRules(), [
    'phpdoc_to_comment' => false,
    'no_blank_lines_after_phpdoc' => false,
]));

$exclusions = [
    'artifacts',
    'test/fixture',
];

if (version_compare(PHP_VERSION, '8.1.x', '<')) {
    $exclusions[] = 'test/src/Test/Enum';
    $exclusions[] = 'test/src/Test/Php82';
}

$config->getFinder()->exclude($exclusions);

return $config;
