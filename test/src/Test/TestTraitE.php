<?php

declare(strict_types=1);

namespace Eloquent\Phony\Test;

trait TestTraitE
{
    public array $constructorArguments;

    public function __construct($first, $second, $third = null)
    {
        $this->constructorArguments = func_get_args();
    }
}
