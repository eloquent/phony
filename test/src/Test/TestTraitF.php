<?php

declare(strict_types=1);

namespace Eloquent\Phony\Test;

trait TestTraitF
{
    private function __construct($first, $second)
    {
        $this->constructorArguments = func_get_args();
    }
}
