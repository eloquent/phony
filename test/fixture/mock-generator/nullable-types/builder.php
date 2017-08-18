<?php

use Eloquent\Phony\Test\TestInterfaceWithNullableTypes;

$builder = $factory->create(
    [
        TestInterfaceWithNullableTypes::class,
        [
            'static customStaticMethod' =>
                function (?string $string, ?stdClass $object) : ?TestClassA {},
            'customMethod' =>
                function (?string $string, ?stdClass $object) : ?TestClassA {},
        ],
    ]
);

return $builder->named('MockGeneratorNullableTypes');
