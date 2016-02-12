<?php

$builder = new Eloquent\Phony\Mock\Builder\MockBuilder(
    array(
        'methodA' => new Eloquent\Phony\Test\TestInvocable(),
    )
);

return $builder->named('Phony\Test\MockGeneratorCustomMethodInvocableObject');
