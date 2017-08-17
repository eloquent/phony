<?php

$builder = $factory->create(
    [
        'Eloquent\Phony\Test\TestInterfaceWithReturnType',
        [
            'customMethodWithClassType' => function () : \stdClass {},
            'customMethodWithScalarType' => function () : int {},
        ],
    ]
);

return $builder->named('MockGeneratorReturnType');
