<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Call\Event;

use Error;
use Exception;

/**
 * The interface implemented by 'received exception' events.
 */
interface ReceivedExceptionEventInterface extends TraversableEventInterface
{
    /**
     * Get the received exception.
     *
     * @return Exception|Error The received exception.
     */
    public function exception();
}
