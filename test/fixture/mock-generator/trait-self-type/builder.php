<?php

use Eloquent\Phony\Test\TestTraitWithSelfType;

$builder = $factory->create(TestTraitWithSelfType::class);

return $builder->named('MockGeneratorTraitSelfType');
