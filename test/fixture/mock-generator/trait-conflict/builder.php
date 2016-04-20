<?php

$builder = $factory->create(
    array(
        'Eloquent\Phony\Test\TestTraitA',
        'Eloquent\Phony\Test\TestTraitB',
        'Eloquent\Phony\Test\TestTraitC',
    )
);

return $builder->named('MockGeneratorTraitConflict');
