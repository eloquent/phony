<?php

$builder = new Eloquent\Phony\Mock\Builder\MockBuilder(
    array(
        'Eloquent\Phony\Test\TestInterfaceWithReturnType',
        array(
            'customMethodWithClassType' => function () : \stdClass {},
            'customMethodWithScalarType' => function () : int {},
        ),
    )
);

return $builder->named('MockGeneratorReturnType');
