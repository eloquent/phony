<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2016 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Cardinality\Exception;

use Exception;

/**
 * The specified cardinality is invalid.
 */
final class InvalidCardinalityException extends Exception implements
    InvalidCardinalityExceptionInterface
{
    /**
     * Construct a new invalid cardinality exception.
     */
    public function __construct()
    {
        parent::__construct('Invalid cardinality.');
    }
}
