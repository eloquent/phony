<?php

if (!version_compare(PHP_VERSION, '5.4.0-dev', '>=')) {
    $this->markTestSkipped('PHP 5.4.0-dev (or later) is required.');
}

return new Eloquent\Phony\Mock\Builder\MockBuilder(
    null,
    array(
        'methodA' =>
            function ($self, callable $first, callable $second = null) {},
    ),
    'MockGeneratorCallableTypeHint'
);
