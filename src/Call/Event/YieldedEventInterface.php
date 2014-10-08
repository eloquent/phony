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
 * The interface implemented by 'yielded' events.
 */
interface YieldedEventInterface extends GeneratorEventInterface
{
    /**
     * Get the yielded key.
     *
     * @return mixed The yielded key.
     */
    public function key();

    /**
     * Get the yielded value.
     *
     * @return mixed The yielded value.
     */
    public function value();
}
