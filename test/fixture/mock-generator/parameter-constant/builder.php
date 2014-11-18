<?php

return new Eloquent\Phony\Mock\Builder\MockBuilder(
    null,
    array(
        'methodA' =>
            function ($phonySelf, $first = ReflectionMethod::IS_PUBLIC) {},
    ),
    'MockGeneratorParameterConstant'
);
