<?php

$builder = $factory->create(
    array(
        'Eloquent\Phony\Test\TestInterfaceWithReturnType',
        array(
            'customMethodWithClassType' => function () : \stdClass {},
            'customMethodWithScalarType' => function () : int {},
        ),
    )
);

return $builder->named('MockGeneratorReturnType');
