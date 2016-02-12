<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Spy;

use Eloquent\Phony\Call\CallInterface;
use Iterator;

/**
 * The interface used to identify iterator spies.
 */
interface IteratorSpyInterface extends Iterator
{
    /**
     * Get the call.
     *
     * @return CallInterface The call.
     */
    public function call();

    /**
     * Get the iterator.
     *
     * @return Iterator The iterator.
     */
    public function iterator();
}
