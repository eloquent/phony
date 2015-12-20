<?php

return new Eloquent\Phony\Mock\Builder\MockBuilder(
    'Eloquent\Phony\Test\TestInterfaceWithReturnType',
    array(
        'customMethodWithClassType' => function () : \stdClass {},
        'customMethodWithScalarType' => function () : int {},
    ),
    'MockGeneratorReturnType'
);
