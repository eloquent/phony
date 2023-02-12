<?php

declare(strict_types=1);

namespace Eloquent\Phony\Test;

trait TestTraitD
{
    public array $constructorArguments;

    public function __construct($first, $second)
    {
        $this->constructorArguments = func_get_args();
    }
}
