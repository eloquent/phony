<?php

$builder = $factory->create(
    [
        'Eloquent\Phony\Test\TestInterfaceWithNullableBuiltinTypes',
        [
            'static customStaticMethod' =>
                function (?string $string, ?stdClass $object) : ?TestClassA {},
            'customMethod' =>
                function (?string $string, ?stdClass $object) : ?TestClassA {},
        ],
    ]
);

return $builder->named('MockGeneratorNullableBultinTypes');
