<?php

$builder = $factory->create(
    array(
        'methodA' => function (
            $phonySelf,
            Non\Existent $first,
            Non\Existent $second = null
        ) {},
    )
);

return $builder->named('MockGeneratorUndefinedTypeHint');
