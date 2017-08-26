<?php

declare(strict_types=1);

namespace Eloquent\Phony\Call\Event;

/**
 * Represents the start of iteration of a returned value.
 */
class UsedEvent extends AbstractCallEvent implements IterableEvent
{
}
