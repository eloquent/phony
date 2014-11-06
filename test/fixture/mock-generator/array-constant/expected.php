<?php

class MockGeneratorArrayConstant
implements \Eloquent\Phony\Mock\MockInterface
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

    private static $_traitMethods = array();
    private static $_customMethods = array();
    private static $_staticProxy;
    private $_proxy;
}
