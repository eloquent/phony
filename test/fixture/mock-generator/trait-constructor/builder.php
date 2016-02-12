<?php

$builder = new Eloquent\Phony\Mock\Builder\MockBuilder(
    'Eloquent\Phony\Test\TestTraitD'
);

return $builder->named('MockGeneratorTraitConstructor');
