<?php

$builder = $factory->create(
    [
        'Eloquent\Phony\Test\TestTraitD',
        'Eloquent\Phony\Test\TestTraitE',
    ]
);

return $builder->named('MockGeneratorTraitConstructorConflict');
