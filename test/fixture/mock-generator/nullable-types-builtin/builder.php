<?php

use Eloquent\Phony\Test\TestInterfaceWithNullableBuiltinTypes;

$builder = $factory->create(
    [
        TestInterfaceWithNullableBuiltinTypes::class,
        [
            'static customStaticMethod' =>
                function (?string $string, ?stdClass $object) : ?TestClassA {},
            'customMethod' =>
                function (?string $string, ?stdClass $object) : ?TestClassA {},
        ],
    ]
);

return $builder->named('MockGeneratorNullableBultinTypes');
