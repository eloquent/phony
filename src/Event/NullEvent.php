<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Event;

/**
 * A special event that represents the absence of an event.
 *
 * @internal
 */
class NullEvent extends AbstractEvent implements NullEventInterface
{
    /**
     * Get the static instance of this event.
     *
     * @return NullEventInterface The static event.
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Construct a new null event.
     */
    public function __construct()
    {
        parent::__construct(-1, -1.0);
    }

    private static $instance;
}
