<?php

use Eloquent\Phony\Test\TestClassD;

$builder = $factory->create(TestClassD::class);

return $builder->named('MockGeneratorPrivateConstructor');
