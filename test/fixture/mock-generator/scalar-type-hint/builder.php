<?php

$builder = $factory->create(
    [
        'Eloquent\Phony\Test\TestInterfaceWithScalarTypeHint',
        [
            'customMethod' => function (int $int) {}
        ],
    ]
);

return $builder->named('MockGeneratorScalarTypeHint');
