<?php

$builder = $factory->create(
    array(
        'methodA' => new Eloquent\Phony\Test\TestInvocable(),
    )
);

return $builder->named('Phony\Test\MockGeneratorCustomMethodInvocableObject');
