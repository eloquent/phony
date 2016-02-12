<?php

$builder = new Eloquent\Phony\Mock\Builder\MockBuilder(
    array(
        'Eloquent\Phony\Test\TestInterfaceWithScalarTypeHint',
        array('customMethod' => function (int $int) {}),
    )
);

return $builder->named('MockGeneratorScalarTypeHint');
