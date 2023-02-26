<?php

declare(strict_types=1);

namespace Eloquent\Phony\Test;

interface TestInterfaceWithUnionTypes
{
    public static function staticMethodA(string|int $a): string|int;

    public function methodA(string|int $a): string|int;
}
