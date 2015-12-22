<?php

$builder = new Eloquent\Phony\Mock\Builder\MockBuilder(
    array(
        'methodA' => function (stdClass $first = null, $second) {},
    )
);

return $builder->named('Phony\Test\MockGeneratorNonOptionalNullableObject');
