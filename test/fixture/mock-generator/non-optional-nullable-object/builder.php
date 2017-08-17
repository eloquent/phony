<?php

$builder = $factory->create(
    [
        'methodA' => function (stdClass $first = null, $second) {},
    ]
);

return $builder->named('Phony\Test\MockGeneratorNonOptionalNullableObject');
