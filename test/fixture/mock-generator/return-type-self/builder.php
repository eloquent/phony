<?php

use Eloquent\Phony\Test\TestInterfaceWithSelfReturnType;

$builder = $factory->create(TestInterfaceWithSelfReturnType::class);

return $builder->named('MockGeneratorSelfReturnType');
