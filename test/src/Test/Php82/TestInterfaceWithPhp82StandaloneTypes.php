<?php

declare(strict_types=1);

namespace Eloquent\Phony\Test\Php82;

interface TestInterfaceWithPhp82StandaloneTypes
{
    public static function staticMethodA(true $a): true;

    public static function staticMethodB(false $a): false;

    public static function staticMethodC(null $a): null;

    public function methodA(true $a): true;

    public function methodB(false $a): false;

    public function methodC(null $a): null;
}
