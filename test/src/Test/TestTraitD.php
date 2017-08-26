<?php

namespace Eloquent\Phony\Test;

trait TestTraitD
{
    public function __construct($first, $second)
    {
        $this->constructorArguments = func_get_args();
    }
}
