<?php

use Eloquent\Phony\Test\Enum\TestInterfaceUsingEnums;

$builder = $factory->create(TestInterfaceUsingEnums::class);

return $builder->named('MockGeneratorEnumTypes');
