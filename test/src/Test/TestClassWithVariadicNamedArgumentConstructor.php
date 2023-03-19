<?php

declare(strict_types=1);

namespace Eloquent\Phony\Test;

class TestClassWithVariadicNamedArgumentConstructor
{
    public array $arguments;

    public function __construct(&$a, &$b, &...$arguments)
    {
        $this->arguments = [&$a, &$b];

        foreach ($arguments as $positionOrName => &$value) {
            $this->arguments[$positionOrName] = &$value;
        }
    }
}
