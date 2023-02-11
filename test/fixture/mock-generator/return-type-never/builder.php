<?php

use Eloquent\Phony\Test\TestInterfaceWithNeverReturnType;

$builder = $factory->create(
    [
        TestInterfaceWithNeverReturnType::class,
        [
            'customMethod' => function () : never {},
        ],
    ]
);

return $builder->named('MockGeneratorNeverReturnType');
