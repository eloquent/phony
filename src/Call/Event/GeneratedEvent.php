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

use Generator;

/**
 * Represents the returning of a generator.
 *
 * @internal
 */
class GeneratedEvent extends AbstractCallEvent implements
    GeneratedEventInterface
{
    /**
     * Construct a 'generated' event.
     *
     * @param integer   $sequenceNumber The sequence number.
     * @param float     $time           The time at which the event occurred, in seconds since the Unix epoch.
     * @param Generator $generator      The generator.
     */
    public function __construct(
        $sequenceNumber,
        $time,
        Generator $generator = null
    ) {
        if (null === $generator) {
            $generator = call_user_func(
                function () {
                    return;
                    yield; // @codeCoverageIgnoreStart
                } // @codeCoverageIgnoreEnd
            );
        }

        parent::__construct($sequenceNumber, $time);

        $this->generator = $generator;
    }

    /**
     * Get the generator.
     *
     * @return Generator The generator.
     */
    public function generator()
    {
        return $this->generator;
    }

    private $generator;
}
