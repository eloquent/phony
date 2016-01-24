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
 * The interface implemented by 'produced' events.
 */
interface ProducedEventInterface extends TraversableEventInterface
{
    /**
     * Get the produced key.
     *
     * @return mixed The produced key.
     */
    public function key();

    /**
     * Get the produced value.
     *
     * @return mixed The produced value.
     */
    public function value();
}
