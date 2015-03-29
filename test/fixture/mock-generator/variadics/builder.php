<?php

return new Eloquent\Phony\Mock\Builder\MockBuilder(
    null,
    array(
        'methodA' => function ($a, $b, ...$c) {},
        'methodB' => function ($a, $b, stdClass ...$c) {},
        'methodC' => function ($a, $b, &...$c) {},
    ),
    'Phony\Test\MockGeneratorVariadics'
);
