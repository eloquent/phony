<?php

return new Eloquent\Phony\Mock\Builder\MockBuilder(
    null,
    array(
        'methodA' => function (
            $phonySelf,
            Non\Existent $first,
            Non\Existent $second = null
        ) {},
    ),
    'MockGeneratorUndefinedTypeHint'
);
