<?php

declare(strict_types=1);

namespace Eloquent\Phony\Stub\Exception;

use Exception;

/**
 * No answer was defined, or the answer is incomplete.
 */
final class UndefinedAnswerException extends Exception
{
    /**
     * Construct a new undefined answer exception.
     */
    public function __construct()
    {
        parent::__construct(
            'No answer was defined, or the answer is incomplete.'
        );
    }
}
