<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Difference;

/**
 * The interface implemented by differences.
 */
interface DifferenceInterface
{
    /**
     * Get the 'from' side of the difference.
     *
     * @return array<integer,mixed> The 'from' side.
     */
    public function from();

    /**
     * Get the 'to' side of the difference.
     *
     * @return array<integer,mixed> The 'to' side.
     */
    public function to();
}
