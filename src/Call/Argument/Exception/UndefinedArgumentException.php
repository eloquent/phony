<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Call\Argument\Exception;

use Exception;

/**
 * Thrown when an argument that was requested by index does not exist.
 *
 * @api
 */
final class UndefinedArgumentException extends Exception
{
    /**
     * Construct a new undefined argument exception.
     *
     * @param integer        $index The index.
     * @param Exception|null $cause The cause, if available.
     */
    public function __construct($index, Exception $cause = null)
    {
        $this->index = $index;

        parent::__construct(
            sprintf(
                'No argument defined for index %s.',
                var_export($index, true)
            ),
            0,
            $cause
        );
    }

    /**
     * Get the index.
     *
     * @api
     *
     * @return integer The index.
     */
    public function index()
    {
        return $this->index;
    }

    private $index;
}
