<?php

$builder = new Eloquent\Phony\Mock\Builder\MockBuilder(
    array(
        'Eloquent\Phony\Test\TestTraitD',
        'Eloquent\Phony\Test\TestTraitE',
    )
);

return $builder->named('MockGeneratorTraitConstructorConflict');
