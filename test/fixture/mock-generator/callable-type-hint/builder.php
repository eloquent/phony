<?php

$builder = $factory->create(
    [
        'methodA' => function (
            $phonySelf,
            callable $first,
            callable $second = null
        ) {},
    ]
);

return $builder->named('MockGeneratorCallableTypeHint');
