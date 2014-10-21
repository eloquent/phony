<?php

return new Eloquent\Phony\Mock\Builder\MockBuilder(
    null,
    array(
        'methodA' =>
            function ($self, callable $first, callable $second = null) {},
    ),
    'MockGeneratorCallableTypeHint'
);
