<?php

$builder = new Eloquent\Phony\Mock\Builder\MockBuilder(
    null,
    null,
    'MockGeneratorArrayConstant'
);
$builder
    ->addConstant('CONSTANT_A', array())
    ->addConstant('CONSTANT_B', array('a', 'b'))
    ->addConstant('CONSTANT_C', array('a' => 'b', 'c' => 'd'));

return $builder;
