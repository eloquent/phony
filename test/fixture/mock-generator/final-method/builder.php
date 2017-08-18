<?php

use Eloquent\Phony\Test\TestClassF;

$builder = $factory->create(TestClassF::class);

return $builder->named('Phony\Test\MockGeneratorFinalMethod');
