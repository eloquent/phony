<?php

$functionName = 'functionName';
$namespace = 'Foo\Bar';
$callback = function (
    $first,
    &$second,
    $third = null,
    $fourth = 111,
    $fifth = array(),
    $sixth = array('valueA', 'valueB'),
    $seventh = array('keyA' => 'valueA', 'keyB' => 'valueB'),
    Eloquent\Phony\Test\TestClassA $eighth,
    Eloquent\Phony\Test\TestClassA $ninth = null,
    array $tenth = array(),
    array $eleventh = null
) {};
