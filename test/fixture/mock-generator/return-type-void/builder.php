<?php

$builder = $factory->create(
    array(
        'Eloquent\Phony\Test\TestInterfaceWithVoidReturnType',
        array(
            'customMethod' => function () : void {},
        ),
    )
);

return $builder->named('MockGeneratorVoidReturnType');
