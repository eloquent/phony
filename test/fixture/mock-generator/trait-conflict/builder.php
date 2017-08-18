<?php

use Eloquent\Phony\Test\TestTraitA;
use Eloquent\Phony\Test\TestTraitB;
use Eloquent\Phony\Test\TestTraitC;

$builder = $factory->create(
    [
        TestTraitA::class,
        TestTraitB::class,
        TestTraitC::class,
    ]
);

return $builder->named('MockGeneratorTraitConflict');
