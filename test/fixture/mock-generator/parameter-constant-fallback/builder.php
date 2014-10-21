<?php

return new Eloquent\Phony\Mock\Builder\MockBuilder(
    null,
    array(
        'methodA' => function ($self, $first = ReflectionMethod::IS_PUBLIC) {},
    ),
    'MockGeneratorParameterConstantFallback'
);
