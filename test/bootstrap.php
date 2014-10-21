<?php

$classLoader = require __DIR__ . '/../vendor/autoload.php';
$classLoader->addPsr4('Eloquent\Phony\\', __DIR__ . '/src');

require_once __DIR__ . '/src/TestClassOldConstructor.php';
