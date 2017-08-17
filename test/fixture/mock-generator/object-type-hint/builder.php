<?php

$builder = $factory->create(
    array(
        'methodA' => function (
            $phonySelf,
            object $first,
            object $second = null
        ) {},
    )
);

return $builder->named('MockGeneratorObjectTypeHint');
