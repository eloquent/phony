<?php

$builder = $factory->create(
    array(
        'methodA' => function (
            $phonySelf,
            iterable $first,
            iterable $second = null
        ) {},
    )
);

return $builder->named('MockGeneratorIterableTypeHint');
