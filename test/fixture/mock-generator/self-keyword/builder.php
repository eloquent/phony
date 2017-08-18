<?php

use Eloquent\Phony\Test\TestClassC;

$builder = $factory->create(TestClassC::class);

return $builder->named('MockGeneratorSelfKeyword');
