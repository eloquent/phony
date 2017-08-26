<?php

declare(strict_types=1);

namespace Eloquent\Phony\Spy;

use ArrayAccess;
use Countable;
use Iterator;

/**
 * The interface implemented by iterable spies.
 */
interface IterableSpy extends ArrayAccess, Countable, Iterator
{
    /**
     * Get the original iterable value.
     *
     * @return iterable The original value.
     */
    public function iterable();
}
