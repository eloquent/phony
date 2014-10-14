<?php

if (defined('HHVM_VERSION')) {
    $this->markTestSkipped('Not supported under HHVM.');
}
if (!version_compare(PHP_VERSION, '5.4.0-dev', '>=')) {
    $this->markTestSkipped('PHP 5.4.0-dev (or later) is required.');
}

return new Eloquent\Phony\Mock\Builder\MockBuilder(
    null,
    array(
        'methodA' => function ($self, $first = ReflectionMethod::IS_PUBLIC) {},
    ),
    'MockGeneratorParameterConstant',
    111
);
