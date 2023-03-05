<?php

$builder = $factory->create(
    [
        'methodA' => function ($a, $b, ...$c) {},
        'methodB' => function ($a, $b, stdClass ...$c) {},
        'methodC' => function ($a, $b, &...$c) {},
        'methodD' => function ($a, $b, ?stdClass...$c) {},
        'methodE' => function (...$a) {},
    ]
);

return $builder->named('Phony\Test\MockGeneratorVariadics');
