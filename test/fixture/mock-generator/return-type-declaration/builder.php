<?php

return new Eloquent\Phony\Mock\Builder\MockBuilder(
    'Eloquent\Phony\Test\TestInterfaceWithReturnTypeDeclarations',
    array(
        'customMethodWithClassType' => function () : \stdClass {},
        'customMethodWithScalarType' => function () : int {},
    ),
    'MockGeneratorReturnTypeDeclaration'
);
