<?php

namespace Eloquent\Phony\Test;

trait TestTraitF
{
    private function __construct($first, $second)
    {
        $this->constructorArguments = func_get_args();
    }
}
