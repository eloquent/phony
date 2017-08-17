<?php

$builder = $factory->create(
    [
        'methodA' => function (
            $phonySelf,
            iterable $first,
            iterable $second = null
        ) {},
    ]
);

return $builder->named('MockGeneratorIterableTypeHint');
