<?php

$builder = new Eloquent\Phony\Mock\Builder\MockBuilder(
    'Eloquent\Phony\Test\TestClassD'
);

return $builder->named('MockGeneratorPrivateConstructor');
