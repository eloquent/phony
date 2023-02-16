<?php

use Eloquent\Phony\Test\Php81\TestInterfaceUsingEnums;

$builder = $factory->create(TestInterfaceUsingEnums::class);

return $builder->named('MockGeneratorEnumTypes');
