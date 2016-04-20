<?php

$builder = $factory->create(
    array(
        'methodA' => function (stdClass $first = null, $second) {},
    )
);

return $builder->named('Phony\Test\MockGeneratorNonOptionalNullableObject');
