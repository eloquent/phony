<?php

$builder = $factory->create(
    [
        'methodA' => function (
            $phonySelf,
            Non\Existent $first,
            Non\Existent $second = null
        ) {},
    ]
);

return $builder->named('MockGeneratorUndefinedTypeHint');
