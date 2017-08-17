<?php

$builder = $factory->create(
    [
        'methodA' => function (
            $phonySelf,
            $first = ReflectionMethod::IS_PUBLIC
        ) {},
    ]
);

return $builder->named('MockGeneratorParameterConstant');
