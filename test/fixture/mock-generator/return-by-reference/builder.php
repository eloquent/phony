<?php

use Eloquent\Phony\Test\TestClassG;

$builder = $factory->create(TestClassG::class);

return $builder->named('MockGeneratorReturnByReference');
