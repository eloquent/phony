<?php

return new Eloquent\Phony\Mock\Builder\MockBuilder(
    null,
    array(
        'methodA' => function (stdClass $first = null, $second) {},
    ),
    'Phony\Test\MockGeneratorNonOptionalNullableObject'
);
