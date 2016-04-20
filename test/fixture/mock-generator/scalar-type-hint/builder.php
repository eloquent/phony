<?php

$builder = $factory->create(
    array(
        'Eloquent\Phony\Test\TestInterfaceWithScalarTypeHint',
        array('customMethod' => function (int $int) {}),
    )
);

return $builder->named('MockGeneratorScalarTypeHint');
