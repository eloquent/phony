<?php

use Eloquent\Phony\Test\TestClassWithTypedProperties;

$builder = $factory->create(
    [
        TestClassWithTypedProperties::class,
    ]
);

return $builder->named('MockGeneratorTypedProperties');
