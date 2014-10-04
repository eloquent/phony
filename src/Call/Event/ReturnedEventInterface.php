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

/**
 * The interface implemented by 'returned' events.
 */
interface ReturnedEventInterface extends ResponseEventInterface
{
    /**
     * Get the returned value.
     *
     * @return mixed The returned value.
     */
    public function returnValue();
}
