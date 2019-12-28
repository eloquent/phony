<?php

declare(strict_types=1);

namespace Eloquent\Phony\Spy;

use ArrayAccess;
use Countable;
use Iterator;

/**
 * The interface implemented by iterable spies.
 *
 * @extends ArrayAccess<mixed,mixed>
 * @extends Iterator<mixed>
 */
interface IterableSpy extends ArrayAccess, Countable, Iterator
{
    /**
     * Get the original iterable value.
     *
     * @return iterable<mixed> The original value.
     */
    public function iterable(): iterable;
}
