<?php

use Eloquent\Phony\Test\TestClassWithVoidReturnType;

$builder = $factory->create(
    [
        TestClassWithVoidReturnType::class,
    ]
);

return $builder->named('MockGeneratorVoidReturnTypeWithParent');
