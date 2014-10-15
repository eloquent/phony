<?php

return new Eloquent\Phony\Mock\Builder\MockBuilder(
    null,
    array(
        'methodA' => function (
            $self,
            Non\Existent $first,
            Non\Existent $second = null
        ) {},
    ),
    'MockGeneratorUndefinedTypeHint'
);
