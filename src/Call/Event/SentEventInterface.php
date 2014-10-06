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
 * The interface implemented by 'sent' events.
 */
interface SentEventInterface extends GeneratorEventInterface
{
    /**
     * Get the sent value.
     *
     * @return mixed The sent value.
     */
    public function value();
}
