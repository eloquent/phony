<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Call\Event;

use Eloquent\Phony\Call\Argument\Exception\UndefinedArgumentException;
use Eloquent\Phony\Event\EventCollectionInterface;

/**
 * The interface implemented by call event collections.
 */
interface CallEventCollectionInterface extends EventCollectionInterface
{
    /**
     * Get the arguments.
     *
     * @return array<integer,mixed>|null The arguments, or null if no arguments were recorded.
     */
    public function arguments();

    /**
     * Get an argument by index.
     *
     * @param integer|null $index The index, or null for the first argument.
     *
     * @return mixed                      The argument.
     * @throws UndefinedArgumentException If the requested argument is undefined, or no arguments were recorded.
     */
    public function argument($index = null);
}
