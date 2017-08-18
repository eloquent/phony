<?php

use Eloquent\Phony\Test\TestInterfaceWithScalarTypeHint;

$builder = $factory->create(
    [
        TestInterfaceWithScalarTypeHint::class,
        [
            'customMethod' => function (int $int) {}
        ],
    ]
);

return $builder->named('MockGeneratorScalarTypeHint');
