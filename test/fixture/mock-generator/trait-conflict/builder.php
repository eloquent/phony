<?php

$builder = new Eloquent\Phony\Mock\Builder\MockBuilder(
    array(
        'Eloquent\Phony\Test\TestTraitA',
        'Eloquent\Phony\Test\TestTraitB',
        'Eloquent\Phony\Test\TestTraitC',
    )
);

return $builder->named('MockGeneratorTraitConflict');
