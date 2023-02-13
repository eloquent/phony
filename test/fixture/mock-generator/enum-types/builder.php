<?php

use Eloquent\Phony\Test\TestInterfaceUsingEnums;

$builder = $factory->create(TestInterfaceUsingEnums::class);

return $builder->named('MockGeneratorEnumTypes');
