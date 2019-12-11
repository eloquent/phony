<?php

declare(strict_types=1);

namespace Eloquent\Phony\Stub\Exception;

use Exception;
use Throwable;

/**
 * Unable to generate a default value for a function with a final return type.
 */
final class FinalReturnTypeException extends Exception
{
    /**
     * Construct a new final return type exception.
     *
     * @param string    $subject A string representation of the relevant callable.
     * @param string    $type    The type that is final.
     * @param Throwable $cause   The cause.
     */
    public function __construct(string $subject, string $type, Throwable $cause)
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
