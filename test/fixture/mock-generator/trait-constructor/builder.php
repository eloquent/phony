<?php

use Eloquent\Phony\Test\TestTraitD;

$builder = $factory->create(TestTraitD::class);

return $builder->named('MockGeneratorTraitConstructor');
