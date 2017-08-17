<?php

$functionName = 'functionName';
$namespace = 'Foo\Bar';
$callback = function (
    $first,
    &$second,
    $third = null,
    $fourth = 111,
    $fifth = [],
    $sixth = ['valueA', 'valueB'],
    $seventh = ['keyA' => 'valueA', 'keyB' => 'valueB'],
    Eloquent\Phony\Test\TestClassA $eighth,
    Eloquent\Phony\Test\TestClassA $ninth = null,
    array $tenth = [],
    array $eleventh = null
) {};
