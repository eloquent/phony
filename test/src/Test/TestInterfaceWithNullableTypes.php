<?php

declare(strict_types=1);

namespace Eloquent\Phony\Test;

use stdClass;

interface TestInterfaceWithNullableTypes
{
    public function staticMethodA(? string $string, ? stdClass $object): ? TestClassA;

    public function staticMethodB(): ? TestClassB;

    public function methodA(? string $string, ? stdClass $object): ? TestClassA;

    public function methodB(): ? TestClassB;

    public function __call($name, array $arguments): ? TestClassA;

    public static function __callStatic($name, array $arguments): ? TestClassA;
}
