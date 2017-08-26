<?php

declare(strict_types=1);

namespace Eloquent\Phony\Mock\Exception;

use Exception;

/**
 * Anonymous classes cannot be mocked.
 */
final class AnonymousClassException extends Exception implements MockException
{
    /**
     * Construct an anonymous class exception.
     */
    public function __construct()
    {
        parent::__construct('Anonymous classes cannot be mocked.');
    }
}
