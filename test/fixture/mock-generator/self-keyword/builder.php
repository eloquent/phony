<?php

$builder = new Eloquent\Phony\Mock\Builder\MockBuilder(
    'Eloquent\Phony\Test\TestClassC'
);

return $builder->named('MockGeneratorSelfKeyword');
