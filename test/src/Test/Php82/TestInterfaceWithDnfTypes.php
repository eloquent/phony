<?php

declare(strict_types=1);

namespace Eloquent\Phony\Test\Php82;

use Countable;
use Iterator;
use IteratorAggregate;

interface TestInterfaceWithDnfTypes
{
    public static function staticMethodA(
        (Countable&Iterator)|(Countable&IteratorAggregate) $a
    ): (Countable&Iterator)|(Countable&IteratorAggregate);

    public function methodA(
        (Countable&Iterator)|(Countable&IteratorAggregate) $a
    ): (Countable&Iterator)|(Countable&IteratorAggregate);
}
