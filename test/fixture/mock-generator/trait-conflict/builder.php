<?php

$builder = $factory->create(
    [
        'Eloquent\Phony\Test\TestTraitA',
        'Eloquent\Phony\Test\TestTraitB',
        'Eloquent\Phony\Test\TestTraitC',
    ]
);

return $builder->named('MockGeneratorTraitConflict');
