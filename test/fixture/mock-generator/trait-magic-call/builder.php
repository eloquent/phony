<?php

use Eloquent\Phony\Test\TestTraitJ;

$builder = $factory->create(TestTraitJ::class);

return $builder->named('MockGeneratorTraitMagicCall');
