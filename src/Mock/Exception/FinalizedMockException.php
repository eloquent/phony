<?php

declare(strict_types=1);

namespace Eloquent\Phony\Mock\Exception;

use Exception;

/**
 * Unable to modify a finalized mock.
 */
final class FinalizedMockException extends Exception implements
    MockException
{
    /**
     * Construct a finalized mock exception.
     */
    public function __construct()
    {
        parent::__construct('Unable to modify a finalized mock.');
    }
}
