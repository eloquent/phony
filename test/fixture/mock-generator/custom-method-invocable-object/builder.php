<?php

return new Eloquent\Phony\Mock\Builder\MockBuilder(
    null,
    array(
        'methodA' => new Eloquent\Phony\Test\TestInvocable(),
    ),
    'Phony\Test\MockGeneratorCustomMethodInvocableObject'
);
