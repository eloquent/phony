<?php

$builder = new Eloquent\Phony\Mock\Builder\MockBuilder(
    array(
        'methodA' => function (
            $phonySelf,
            Non\Existent $first,
            Non\Existent $second = null
        ) {},
    )
);

return $builder->named('MockGeneratorUndefinedTypeHint');
