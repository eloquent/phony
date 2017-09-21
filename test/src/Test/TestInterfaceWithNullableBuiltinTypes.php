<?php

declare(strict_types=1);

namespace Eloquent\Phony\Test;

interface TestInterfaceWithNullableBuiltinTypes
{
    public function staticMethodA(? string $string, ? int $object): ? bool;

    public function staticMethodB(): ? int;

    public function methodA(? string $string, ? int $object): ? bool;

    public function methodB(): ? int;

    public function __call($name, array $arguments): ? int;

    public static function __callStatic($name, array $arguments): ? int;
}
