<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Call\Exception;

use Exception;

/**
 * An undefined call was requested.
 *
 * @api
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
            sprintf('No call defined for index %s.', var_export($index, true)),
            0,
            $cause
        );
    }

    /**
     * Get the call index.
     *
     * @api
     *
     * @return integer The call index.
     */
    public function index()
    {
        return $this->index;
    }

    private $index;
}
