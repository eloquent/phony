<?php

$builder = new Eloquent\Phony\Mock\Builder\MockBuilder(
    'Eloquent\Phony\Test\TestTraitJ'
);

return $builder->named('MockGeneratorTraitMagicCall');
