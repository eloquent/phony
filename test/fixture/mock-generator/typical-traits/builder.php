<?php

return new Eloquent\Phony\Mock\Builder\MockBuilder(
    array(
        'Eloquent\Phony\Test\TestClassB',
        'Iterator',
        'Countable',
        'ArrayAccess',
        'Eloquent\Phony\Test\TestTraitA',
        'Eloquent\Phony\Test\TestTraitB'
    ),
    array(
        'const CONSTANT_A' => 'constantValueA',
        'const CONSTANT_B' => 444,
        'static methodA' => function ($phonySelf, $first, &$second) {},
        'static methodB' =>
            function (
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
                Eloquent\Phony\Mock\MockInterface $phonySelf,
                Eloquent\Phony\Test\TestClassA $first,
                Eloquent\Phony\Test\TestClassA $second = null,
                array $third = array(),
                array $fourth = null
            ) {},
        'methodD' => function ($phonySelf) {},
        'propertyC' => 'valueC',
        'propertyD' => 333,
    ),
    'Phony\Test\MockGeneratorTypicalTraits'
);
