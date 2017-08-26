<?php

namespace Eloquent\Phony\Test;

interface TestInterfaceWithScalarTypeHint
{
    public function method(
        int $a,
        float $b,
        string $c,
        bool $d
    );
}
