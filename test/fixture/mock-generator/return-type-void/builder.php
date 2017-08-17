<?php

$builder = $factory->create(
    [
        'Eloquent\Phony\Test\TestInterfaceWithVoidReturnType',
        [
            'customMethod' => function () : void {},
        ],
    ]
);

return $builder->named('MockGeneratorVoidReturnType');
