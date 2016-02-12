<?php

$builder = new Eloquent\Phony\Mock\Builder\MockBuilder(
    array(
        'methodA' => function (
            $phonySelf,
            callable $first,
            callable $second = null
        ) {},
    )
);

return $builder->named('MockGeneratorCallableTypeHint');
