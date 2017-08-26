<?php

namespace Eloquent\Phony\Test;

trait TestTraitE
{
    public function __construct($first, $second, $third = null)
    {
        $this->constructorArguments = func_get_args();
    }
}
