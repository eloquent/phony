<?php

$builder = new Eloquent\Phony\Mock\Builder\MockBuilder(
    array(
        'stdClass',
        'Iterator',
        'Countable',
        'ArrayAccess'
    ),
    array(
        'static methodA' => function ($className, $first, &$second) {
            return 'methodA';
        },
        'static methodB' =>
            function (
                $className,
                $first = null,
                $second = 111,
                $third = array(),
                $fourth = array('valueA', 'valueB'),
                $fifth = array('keyA' => 'valueA', 'keyB' => 'valueB')
            ) {
                return 'methodB';
            },
        'static propertyA' => 'valueA',
        'static propertyB' => 222,
        'methodC' =>
            function (
                Eloquent\Phony\Mock\MockInterface $self,
                Eloquent\Phony\Test\TestClass $first,
                Eloquent\Phony\Test\TestClass $second = null,
                array $third,
                array $fourth = null
            ) {
                return 'methodC';
            },
        'methodD' => function ($self) {
            return 'methodD';
        },
        'propertyC' => 'valueC',
        'propertyD' => 333,
    ),
    null,
    111
);
$builder
    ->addConstant('CONSTANT_A', 'constantValueA')
    ->addConstant('CONSTANT_B', 444)
    ->addConstant('CONSTANT_C', array())
    ->addConstant('CONSTANT_D', array('valueA', 'valueB'))
    ->addConstant('CONSTANT_E', array('keyA' => 'valueA', 'keyB' => 'valueB'));

return $builder;
