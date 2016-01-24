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
}
