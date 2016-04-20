<?php

$builder = $factory->create(
    array(
        'methodA' => function ($a, $b, ...$c) {},
        'methodB' => function ($a, $b, stdClass ...$c) {},
        'methodC' => function ($a, $b, &...$c) {},
    )
);

return $builder->named('Phony\Test\MockGeneratorVariadics');
