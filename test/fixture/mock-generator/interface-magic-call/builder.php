<?php

use Eloquent\Phony\Test\TestInterfaceD;

$builder = $factory->create(TestInterfaceD::class);

return $builder->named('MockGeneratorInterfaceMagicCall');
