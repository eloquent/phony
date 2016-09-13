<?php

$builder = $factory->create(
    array(
        'Eloquent\Phony\Test\TestInterfaceWithNullableTypes',
        array(
            'static customStaticMethod' =>
                function (?string $string, ?stdClass $object) : ?TestClassA {},
            'customMethod' =>
                function (?string $string, ?stdClass $object) : ?TestClassA {},
        ),
    )
);

return $builder->named('MockGeneratorNullableTypes');
