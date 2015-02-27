<?php

return new Eloquent\Phony\Mock\Builder\MockBuilder(
    array(
        'Eloquent\Phony\Test\TestTraitD',
        'Eloquent\Phony\Test\TestTraitE',
    ),
    null,
    'MockGeneratorTraitConstructorConflict'
);
