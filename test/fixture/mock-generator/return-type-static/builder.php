<?php

use Eloquent\Phony\Test\TestInterfaceWithStaticReturnType;

$builder = $factory->create(TestInterfaceWithStaticReturnType::class);

return $builder->named('MockGeneratorStaticReturnType');
