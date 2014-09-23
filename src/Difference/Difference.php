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
 * Represents a difference between to sets.
 */
class Difference implements DifferenceInterface
{
    /**
     * Construct a new difference.
     *
     * @param array<integer,mixed> $from The 'from' side.
     * @param array<integer,mixed> $to   The 'to' side.
     */
    public function __construct(array $from, array $to)
    {
        $this->from = $from;
        $this->to = $to;
    }

    /**
     * Get the 'from' side of the difference.
     *
     * @return array<integer,mixed> The 'from' side.
     */
    public function from()
    {
        return $this->from;
    }

    /**
     * Get the 'to' side of the difference.
     *
     * @return array<integer,mixed> The 'to' side.
     */
    public function to()
    {
        return $this->to;
    }

    private $from;
    private $to;
}
