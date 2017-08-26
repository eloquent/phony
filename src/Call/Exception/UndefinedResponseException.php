<?php

declare(strict_types=1);

namespace Eloquent\Phony\Call\Exception;

use Exception;

/**
 * The call has not yet produced a response of the requested type.
 */
final class UndefinedResponseException extends Exception
{
    /**
     * Construct a new undefined return value exception.
     *
     * @param string $message The message.
     */
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
