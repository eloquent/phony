<?php

declare(strict_types=1);

namespace Eloquent\Phony\Test;

interface TestInterfaceWithSelfReturnType
{
    public static function staticMethod(): self;

    public static function __callStatic($name, array $arguments): self;

    public function method(): self;

    public function __call($name, array $arguments): self;
}
