<?php

declare(strict_types=1);

namespace Eloquent\Phony\Test;

class TestClassWithVariadicNamedArguments
{
    public array $arguments;

    public function setArguments(...$arguments)
    {
        $this->arguments = $arguments;
    }
}
