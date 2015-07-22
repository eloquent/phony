<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2015 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Collection\Exception;

use Exception;

/**
 * No argument is defined for the requested index.
 */
final class UndefinedIndexException extends Exception
{
    /**
     * Construct a new undefined index exception.
     *
     * @param integer        $index The index.
     * @param Exception|null $cause The cause, if available.
     */
    public function __construct($index, Exception $cause = null)
    {
        $this->index = $index;

        parent::__construct(
            sprintf('Undefined index %s.', var_export($index, true)),
            0,
            $cause
        );
    }

    /**
     * Get the index.
     *
     * @return integer The index.
     */
    public function index()
    {
        return $this->index;
    }

    private $index;
}
