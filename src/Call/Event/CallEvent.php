<?php

declare(strict_types=1);

namespace Eloquent\Phony\Call\Event;

use Eloquent\Phony\Call\Call;
use Eloquent\Phony\Event\Event;

/**
 * The interface implemented by call events.
 */
interface CallEvent extends Event
{
    /**
     * Set the call.
     *
     * @param Call $call The call.
     *
     * @return $this This event.
     */
    public function setCall(Call $call): self;

    /**
     * Get the call.
     *
     * @return ?Call The call, or null if no call has been set.
     */
    public function call(): ?Call;
}
