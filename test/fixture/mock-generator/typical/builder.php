<?php

$builder = new Eloquent\Phony\Mock\Builder\MockBuilder(
    array(
        'Eloquent\Phony\Test\TestClassB',
        'Iterator',
        'Countable',
        'ArrayAccess'
    ),
    array(
        'static methodA' => function ($className, $first, &$second) {},
        'static methodB' =>
            function (
                $className,
                $first = null,
                $second = 111,
                $third = array(),
                $fourth = array('valueA', 'valueB'),
                $fifth = array('keyA' => 'valueA', 'keyB' => 'valueB')
            ) {},
        'static propertyA' => 'valueA',
        'static propertyB' => 222,
        'methodC' =>
            function (
                Eloquent\Phony\Mock\MockInterface $self,
                Eloquent\Phony\Test\TestClassA $first,
                Eloquent\Phony\Test\TestClassA $second = null,
                array $third = array(),
                array $fourth = null
            ) {},
        'methodD' => function ($self) {},
        'propertyC' => 'valueC',
        'propertyD' => 333,
    ),
    'Example\MockGeneratorTypical'
);
$builder
    ->addConstant('CONSTANT_A', 'constantValueA')
    ->addConstant('CONSTANT_B', 444);

return $builder;
