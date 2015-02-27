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

use Eloquent\Phony\Call\Argument\ArgumentsInterface;

/**
 * The interface implemented by 'called' events.
 */
interface CalledEventInterface extends CallEventInterface
{
    /**
     * Get the callback.
     *
     * @return callable The callback.
     */
    public function callback();

    /**
     * Get the received arguments.
     *
     * @return ArgumentsInterface The received arguments.
     */
    public function arguments();
}
