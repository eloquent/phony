<?php

$builder = new Eloquent\Phony\Mock\Builder\MockBuilder(
    array(
        'methodA' => function (
            $phonySelf,
            $first = ReflectionMethod::IS_PUBLIC
        ) {},
    )
);

return $builder->named('MockGeneratorParameterConstant');
