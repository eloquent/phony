<?php

use Eloquent\Phony\Test\TestClassB;
use Eloquent\Phony\Test\TestTraitA;
use Eloquent\Phony\Test\TestTraitB;

$builder = $factory->create(
    [
        TestClassB::class,
        Iterator::class,
        Countable::class,
        ArrayAccess::class,
        TestTraitA::class,
        TestTraitB::class,
        [
            'const CONSTANT_A' => 'constantValueA',
            'const CONSTANT_B' => 444,
            'const CONSTANT_C' => null,
            'static methodA' => function ($phonySelf, $first, &$second) {},
            'static methodB' => function (
                $first = null,
                $second = 111,
                $third = [],
                $fourth = ['valueA', 'valueB'],
                $fifth = ['keyA' => 'valueA', 'keyB' => 'valueB']
            ) {},
            'static propertyA' => 'valueA',
            'static propertyB' => 222,
            'methodC' => function (
                Eloquent\Phony\Mock\Mock $phonySelf,
                Eloquent\Phony\Test\TestClassA $first,
                Eloquent\Phony\Test\TestClassA $second = null,
                array $third = [],
                array $fourth = null
            ) {},
            'methodD' => function ($phonySelf) {},
            'propertyC' => 'valueC',
            'propertyD' => 333,
        ],
    ]
);

return $builder->named('Phony\Test\MockGeneratorTypicalTraits');
