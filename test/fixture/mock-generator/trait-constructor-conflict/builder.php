<?php

$builder = $factory->create(
    array(
        'Eloquent\Phony\Test\TestTraitD',
        'Eloquent\Phony\Test\TestTraitE',
    )
);

return $builder->named('MockGeneratorTraitConstructorConflict');
