<?php

$builder = $factory->create(
    [
        'methodA' => new Eloquent\Phony\Test\TestInvocable(),
    ]
);

return $builder->named('Phony\Test\MockGeneratorCustomMethodInvocableObject');
