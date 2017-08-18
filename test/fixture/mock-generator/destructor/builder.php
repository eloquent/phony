<?php

use Eloquent\Phony\Test\TestClassJ;

$builder = $factory->create(TestClassJ::class);

return $builder->named('MockGeneratorDestructor');
