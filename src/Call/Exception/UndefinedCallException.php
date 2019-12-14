<?php

declare(strict_types=1);

namespace Eloquent\Phony\Call\Exception;

use Exception;

/**
 * An undefined call was requested.
 */
final class UndefinedCallException extends Exception
{
    /**
     * Construct a new undefined call exception.
     *
     * @param int $index The call index.
     */
    public function __construct(int $index)
    {
        $this->index = $index;

        parent::__construct(
            sprintf('No call defined for index %s.', var_export($index, true))
        );
    }

    /**
     * Get the call index.
     *
     * @return int The call index.
     */
    public function index(): int
    {
        return $this->index;
    }

    /**
     * @var int
     */
    private $index;
}
