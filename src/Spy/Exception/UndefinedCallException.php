<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Spy\Exception;

use Exception;

/**
 * An undefined call was requested.
 */
final class UndefinedCallException extends Exception
{
    /**
     * Construct a new undefined call exception.
     *
     * @param integer        $index The call index.
     * @param Exception|null $cause The cause, if available.
     */
    public function __construct($index, Exception $cause = null)
    {
        $this->index = $index;

        parent::__construct(
            sprintf('No call defined for index %d.', $index),
            0,
            $cause
        );
    }

    /**
     * Get the call index.
     *
     * @return integer The call index.
     */
    public function index()
    {
        return $this->index;
    }

    private $index;
}
