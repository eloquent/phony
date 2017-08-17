<?php

$builder = $factory->create(
    [
        'methodA' => function (
            $phonySelf,
            object $first,
            object $second = null
        ) {},
    ]
);

return $builder->named('MockGeneratorObjectTypeHint');
