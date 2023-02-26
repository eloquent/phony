<?php

use Eloquent\Phony\Call\Arguments;
use Eloquent\Phony\Mock\Handle\InstanceHandle;
use Eloquent\Phony\Mock\Handle\StaticHandleRegistry;
use Eloquent\Phony\Mock\Mock;

class MockGeneratorArrayConstant
implements Mock
{
    const CONSTANT_A = array (
);
    const CONSTANT_B = array (
  0 => 'a',
  1 => 'b',
);
    const CONSTANT_C = array (
  'a' => 'b',
  'c' => 'd',
);

    private readonly InstanceHandle $_handle;
}
