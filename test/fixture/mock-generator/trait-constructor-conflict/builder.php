<?php

use Eloquent\Phony\Test\TestTraitD;
use Eloquent\Phony\Test\TestTraitE;

$builder = $factory->create(
    [
        TestTraitD::class,
        TestTraitE::class,
    ]
);

return $builder->named('MockGeneratorTraitConstructorConflict');
