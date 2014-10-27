<?php

$function = new ReflectionFunction(function(
    $a0,
    &$a1,
    array $a2,
    array &$a3,
    Type $a4,
    Type &$a5,
    Namespaced\Type $a6,
    Namespaced\Type &$a7,
    $a0 = 'string',
    &$a1 = 111,
    array $a2 = array('a', 'b', 'c' => 'd'),
    array &$a3 = null,
    Type $a4 = null,
    Type &$a5 = null,
    Namespaced\Type $a6 = null,
    Namespaced\Type &$a7 = null
) {});
$parameters = $function->getParameters();
var_dump(array_map('strval', $parameters));

$function = new ReflectionFunction(function(
    $a0 = ReflectionMethod::IS_FINAL
) {});
$parameters = $function->getParameters();
var_dump(array_map('strval', $parameters));

$function = new ReflectionFunction(function(
    callable $a0,
    callable $a1 = null
) {});
$parameters = $function->getParameters();
var_dump(array_map('strval', $parameters));
