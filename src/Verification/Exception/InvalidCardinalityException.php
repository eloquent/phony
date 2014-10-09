<?php

/*
 * This file is part of the Phony package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Phony\Verification\Exception;

use Exception;

/**
 * The specified cardinality is invalid.
 *
 * @internal
 */
final class InvalidCardinalityException extends Exception implements
    InvalidCardinalityExceptionInterface
{
    /**
     * Construct an invalid cardinality exception.
     *
     * @param Exception|null $cause The cause, if available.
     */
    public function __construct(Exception $cause = null)
    {
        parent::__construct('Invalid cardinality.', 0, $cause);
    }
}
