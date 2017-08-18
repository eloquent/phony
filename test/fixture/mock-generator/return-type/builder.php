<?php

use Eloquent\Phony\Test\TestInterfaceWithReturnType;

$builder = $factory->create(
    [
        TestInterfaceWithReturnType::class,
        [
            'customMethodWithClassType' => function () : \stdClass {},
            'customMethodWithScalarType' => function () : int {},
        ],
    ]
);

return $builder->named('MockGeneratorReturnType');
