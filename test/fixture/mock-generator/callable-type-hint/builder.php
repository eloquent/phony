<?php

return new Eloquent\Phony\Mock\Builder\MockBuilder(
    null,
    array(
        'methodA' =>
            function ($phonySelf, callable $first, callable $second = null) {},
    ),
    'MockGeneratorCallableTypeHint'
);
