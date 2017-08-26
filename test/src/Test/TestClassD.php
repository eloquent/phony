<?php

namespace Eloquent\Phony\Test;

class TestClassD
{
    private function __construct()
    {
        $this->constructorArguments = func_get_args();
    }

    public $constructorArguments;
}
