<?php

$builder = $factory->create(
    array(
        'methodA' => function (
            $phonySelf,
            $first = ReflectionMethod::IS_PUBLIC
        ) {},
    )
);

return $builder->named('MockGeneratorParameterConstant');
