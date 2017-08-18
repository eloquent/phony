<?php

use Eloquent\Phony\Test\TestInterfaceWithVoidReturnType;

$builder = $factory->create(
    [
        TestInterfaceWithVoidReturnType::class,
        [
            'customMethod' => function () : void {},
        ],
    ]
);

return $builder->named('MockGeneratorVoidReturnType');
