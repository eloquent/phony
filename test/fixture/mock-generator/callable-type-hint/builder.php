<?php

$builder = $factory->create(
    array(
        'methodA' => function (
            $phonySelf,
            callable $first,
            callable $second = null
        ) {},
    )
);

return $builder->named('MockGeneratorCallableTypeHint');
