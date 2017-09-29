<?php

declare(strict_types=1);

namespace Eloquent\Phony\Stub\Exception;

use Exception;

/**
 * Unable to generate a default value for a function with a final return type.
 */
final class FinalReturnTypeException extends Exception
{
    /**
     * Construct a new final return type exception.
     */
    public function __construct(string $subject, string $type, Exception $cause)
    {
        parent::__construct(
            sprintf(
                'Unable to create a default return value for %s, which has a ' .
                    'final return type of %s.',
                var_export($subject, true),
                var_export($type, true)
            ),
            0,
            $cause
        );
    }
}
