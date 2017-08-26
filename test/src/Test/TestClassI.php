<?php

namespace Eloquent\Phony\Test;

class TestClassI
{
    final public function __construct($a, $b)
    {
        $this->constructorArguments = func_get_args();
    }

    public $constructorArguments;
}
