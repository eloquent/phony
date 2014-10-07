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
use Eloquent\Phony\Event\AbstractEvent;

/**
 * An abstract base class for implementing call events.
 *
 * @internal
 */
abstract class AbstractCallEvent extends AbstractEvent implements
    CallEventInterface
{
    /**
     * Set the call.
     *
     * @param CallInterface $call The call.
     */
    public function setCall(CallInterface $call)
    {
        $this->call = $call;
    }

    /**
     * Get the call.
     *
     * @return CallInterface|null The call, or null if no call has been set.
     */
    public function call()
    {
        return $this->call;
    }

    private $call;
}
