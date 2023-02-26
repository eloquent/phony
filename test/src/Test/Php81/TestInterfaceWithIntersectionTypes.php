<?php

declare(strict_types=1);

namespace Eloquent\Phony\Test\Php81;

use Countable;
use Iterator;

interface TestInterfaceWithIntersectionTypes
{
    public static function staticMethodA(Countable&Iterator $a): Countable&Iterator;

    public function methodA(Countable&Iterator $a): Countable&Iterator;
}
