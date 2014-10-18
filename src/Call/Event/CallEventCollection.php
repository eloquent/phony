<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Call\Event;

use Eloquent\Phony\Call\CallInterface;
use Eloquent\Phony\Call\Exception\UndefinedArgumentException;
use Eloquent\Phony\Event\EventCollection;

/**
 * Represents a collection of call events.
 *
 * @internal
 */
class CallEventCollection extends EventCollection implements
    CallEventCollectionInterface
{
    /**
     * Get the arguments.
     *
     * @return array<integer,mixed>|null The arguments, or null if no arguments were recorded.
     */
    public function arguments()
    {
        foreach ($this->events as $event) {
            if (
                $event instanceof CallInterface ||
                $event instanceof CalledEventInterface
            ) {
                return $event->arguments();
            }
        }
    }

    /**
     * Get an argument by index.
     *
     * @param integer|null $index The index, or null for the first argument.
     *
     * @return mixed                      The argument.
     * @throws UndefinedArgumentException If the requested argument is undefined, or no arguments were recorded.
     */
    public function argument($index = null)
    {
        foreach ($this->events as $event) {
            if (
                $event instanceof CallInterface ||
                $event instanceof CalledEventInterface
            ) {
                return $event->argument($index);
            }
        }

        throw new UndefinedArgumentException($index);

    }
}
