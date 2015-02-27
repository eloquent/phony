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

/**
 * The interface implemented by 'received' events.
 */
interface ReceivedEventInterface extends TraversableEventInterface
{
    /**
     * Get the received value.
     *
     * @return mixed The received value.
     */
    public function value();
}
