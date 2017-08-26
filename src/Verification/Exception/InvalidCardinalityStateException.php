<?php

declare(strict_types=1);

namespace Eloquent\Phony\Verification\Exception;

use Exception;

/**
 * The requested operation would create an invalid cardinality state.
 */
final class InvalidCardinalityStateException extends Exception implements
    InvalidCardinalityException
{
    /**
     * Construct a new invalid cardinality state exception.
     */
    public function __construct()
    {
        parent::__construct('Invalid cardinality.');
    }
}
