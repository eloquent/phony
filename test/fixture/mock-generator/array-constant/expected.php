<?php

class MockGeneratorArrayConstant
implements \Eloquent\Phony\Mock\Mock
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

    private readonly \Eloquent\Phony\Mock\Handle\InstanceHandle $_handle;
}
